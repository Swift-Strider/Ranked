<?php

/**
 *  ________  ________  ________   ___  __    _______   ________
 * |\   __  \|\   __  \|\   ___  \|\  \|\  \ |\  ___ \ |\   ___ \
 * \ \  \|\  \ \  \|\  \ \  \\ \  \ \  \/  /|\ \   __/|\ \  \_|\ \
 *  \ \   _  _\ \   __  \ \  \\ \  \ \   ___  \ \  \_|/_\ \  \ \\ \
 *   \ \  \\  \\ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \_|\ \ \  \_\\ \
 *    \ \__\\ _\\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \_______\
 *     \|__|\|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|_______|.
 *
 *    Copyright [2022] [DiamondStrider1]
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

declare(strict_types=1);

namespace DiamondStrider1\Ranked\database;

use DiamondStrider1\Ranked\struct\PermissionSet;
use DiamondStrider1\Ranked\struct\Rank;
use DiamondStrider1\Ranked\struct\RankedPlayer;
use DiamondStrider1\Ranked\struct\RankInheritance;
use DiamondStrider1\Ranked\struct\RankInstance;
use Generator;
use Ramsey\Uuid\UuidInterface;
use SOFe\AwaitGenerator\Await;

class Database
{
    public function __construct(private QueryRunner $runner)
    {
    }

    //region Query

    /**
     * @return Generator<mixed, AwaitValue, mixed, array<int, RankInstance>>
     */
    public function listPlayersOfRank(Rank $rank): Generator
    {
        $rank_id = $rank->getId();
        $rows = yield from $this->runner->queryPlayersOfRank($rank_id);
        $rankInstances = [];
        foreach ($rows as $row) {
            $rankInstances[] = Db2Struct::RankInstance($row);
        }

        return array_filter($rankInstances, function (RankInstance $ri) {
            $expirationDate = $ri->getExpirationDate();

            return null === $expirationDate || DateUtils::string2time($expirationDate) > DateUtils::currentTime();
        });
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, array<int, RankInstance>>
     */
    public function listRanksOfPlayer(UuidInterface $player_uuid): Generator
    {
        $player_uuid = $player_uuid->getHex()->toString();
        Await::g2c($this->cleanExpiredRankInstances());
        $rows = yield from $this->runner->queryRanksOfPlayer($player_uuid);
        $rankInstances = [];
        foreach ($rows as $row) {
            $rankInstances[] = Db2Struct::RankInstance($row);
        }

        return array_filter($rankInstances, function (RankInstance $ri) {
            $expirationDate = $ri->getExpirationDate();

            return null === $expirationDate || DateUtils::string2time($expirationDate) > DateUtils::currentTime();
        });
    }

    //endregion Query

    //region Inheritance

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function createInheritance(Rank $child, Rank $parent): Generator
    {
        $child_id = $child->getId();
        $parent_id = $parent->getId();
        yield from $this->runner->inheritanceCreate($child_id, $parent_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function removeInheritance(Rank $child, Rank $parent): Generator
    {
        $child_id = $child->getId();
        $parent_id = $parent->getId();
        yield from $this->runner->inheritanceRemove($child_id, $parent_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, array<int, RankInheritance>>
     */
    public function listRankInheritances(): Generator
    {
        $rows = yield from $this->runner->inheritanceList();
        $rankInheritances = [];
        foreach ($rows as $row) {
            $rankInheritances[] = Db2Struct::RankInheritance($row);
        }

        return $rankInheritances;
    }

    //endregion Inheritance

    //region Ranks

    /**
     * @return Generator<mixed, AwaitValue, mixed, Rank>
     */
    public function createRank(string $name): Generator
    {
        $id = yield from $this->runner->ranksCreate($name);

        return new Rank($id, $name);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function removeRank(Rank $rank): Generator
    {
        $rank_id = $rank->getId();
        yield from $this->runner->ranksRemove($rank_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, array<int, Rank>>
     */
    public function listRanks(): Generator
    {
        $rows = yield from $this->runner->ranksList();
        $ranks = [];
        foreach ($rows as $row) {
            $ranks[] = Db2Struct::Rank($row);
        }

        return $ranks;
    }

    //endregion Ranks

    //region Permissions

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function createRankPermission(Rank $rank, string $permission): Generator
    {
        $rank_id = $rank->getId();
        yield from $this->runner->permissionsCreate($permission, $rank_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function removeRankPermission(Rank $rank, string $permission): Generator
    {
        $rank_id = $rank->getId();
        yield from $this->runner->permissionsRemove($permission, $rank_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, PermissionSet>
     */
    public function listPermissionsOfRank(Rank $rank): Generator
    {
        $rank_id = $rank->getId();
        $result = yield from $this->runner->permissionsList($rank_id);
        $perms = [];
        foreach ($result as $row) {
            $perms[] = $row['permission'];
        }

        return new PermissionSet($perms);
    }

    //endregion Permissions

    //region Players

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function createRankedPlayer(UuidInterface $player_uuid, string $username, string $display_name): Generator
    {
        $player_uuid = $player_uuid->getHex()->toString();
        yield from $this->runner->playersCreate($display_name, $player_uuid, $username);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function removeRankedPlayer(UuidInterface $player_uuid): Generator
    {
        $player_uuid = $player_uuid->getHex()->toString();
        yield from $this->runner->playersRemove($player_uuid);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, array<int, RankedPlayer>>
     */
    public function listRankedPlayers(): Generator
    {
        $rows = yield from $this->runner->playersList();
        $rankedPlayers = [];
        foreach ($rows as $row) {
            $rankedPlayers[] = Db2Struct::RankedPlayer($row);
        }

        return $rankedPlayers;
    }

    //endregion Players

    //region RankInstances

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function createRankInstance(UuidInterface $player_uuid, Rank $rank, string $expiration_date): Generator
    {
        $player_uuid = $player_uuid->getHex()->toString();
        $rank_id = $rank->getId();
        yield from $this->runner->rankInstancesCreate($expiration_date, $player_uuid, $rank_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function removeRankInstance(UuidInterface $player_uuid, Rank $rank): Generator
    {
        $player_uuid = $player_uuid->getHex()->toString();
        $rank_id = $rank->getId();
        yield from $this->runner->rankInstancesRemove($player_uuid, $rank_id);
    }

    /**
     * @return Generator<mixed, AwaitValue, mixed, void>
     */
    public function cleanExpiredRankInstances(): Generator
    {
        yield from $this->runner->rankInstancesCleanExpired(DateUtils::currentTimeString());
    }

    //endregion RankInstances
}
