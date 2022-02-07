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

use Closure;
use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;
use SOFe\RwLock\Mutex;

class QueryRunner
{
    private ?Mutex $sqliteLock;

    public function __construct(
        private DataConnector $db,
        bool $isSqlite,
    ) {
        $this->sqliteLock = $isSqlite ? new Mutex() : null;
    }

    public function init(): Generator
    {
        yield from $this->lock(function (): Generator {
            $this->db->executeGeneric('ranked.init.ranks', [], yield Await::RESOLVE, yield Await::REJECT);
            $this->db->executeGeneric('ranked.init.rankpermissions', [], yield Await::RESOLVE, yield Await::REJECT);
            $this->db->executeGeneric('ranked.init.players', [], yield Await::RESOLVE, yield Await::REJECT);
            $this->db->executeGeneric('ranked.init.rank_players', [], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ALL;
        });
    }

    public function createRank(string $name): Generator
    {
        return yield from $this->lock(function () use ($name): Generator {
            $this->db->executeInsert('ranked.ranks.create', ['name' => $name], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function removeRank(int $id): Generator
    {
        return yield from $this->lock(function () use ($id): Generator {
            $this->db->executeChange('ranked.ranks.remove', ['id' => $id], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function listRanks(): Generator
    {
        return yield from $this->lock(function (): Generator {
            $this->db->executeSelect('ranked.ranks.list', [], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function getRankId(string $name): Generator
    {
        return yield from $this->lock(function () use ($name): Generator {
            $this->db->executeSelect('ranked.ranks.get', ['name' => $name], yield Await::RESOLVE, yield Await::REJECT);

            return (yield Await::ONCE)[0]['id'] ?? null;
        });
    }

    public function setPermission(int $rankId, string $permission): Generator
    {
        yield from $this->lock(function () use ($rankId, $permission): Generator {
            $this->db->executeGeneric('ranked.permissions.set', ['rank_id' => $rankId, 'permission' => $permission], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function unsetPermission(int $rankId, string $permission): Generator
    {
        return yield from $this->lock(function () use ($rankId, $permission): Generator {
            $this->db->executeGeneric('ranked.permissions.unset', ['rank_id' => $rankId, 'permission' => $permission], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function listPermissions(int $rankId): Generator
    {
        return yield from $this->lock(function () use ($rankId): Generator {
            $this->db->executeSelect('ranked.permissions.list', ['rank_id' => $rankId], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function setPlayer(string $uuid, string $username, string $displayName): Generator
    {
        yield from $this->lock(function () use ($uuid, $username, $displayName): Generator {
            $this->db->executeGeneric('ranked.players.set', [
                'player_uuid' => $uuid,
                'username' => $username,
                'display_name' => $displayName,
            ], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function unsetPlayer(string $uuid): Generator
    {
        yield from $this->lock(function () use ($uuid): Generator {
            $this->db->executeGeneric('ranked.players.unset', [
                'player_uuid' => $uuid,
            ], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function listPlayers(): Generator
    {
        return yield from $this->lock(function (): Generator {
            $this->db->executeSelect('ranked.players.list', [], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function setPlayerRank(int $rank_id, string $player_uuid): Generator
    {
        yield from $this->lock(function () use ($rank_id, $player_uuid): Generator {
            $this->db->executeGeneric('ranked.player_ranks.set', [
                'rank_id' => $rank_id,
                'player_uuid' => $player_uuid,
            ], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function unsetPlayerRank(int $rank_id, string $player_uuid): Generator
    {
        yield from $this->lock(function () use ($rank_id, $player_uuid): Generator {
            $this->db->executeGeneric('ranked.player_ranks.unset', [
                'rank_id' => $rank_id,
                'player_uuid' => $player_uuid,
            ], yield Await::RESOLVE, yield Await::REJECT);
            yield Await::ONCE;
        });
    }

    public function listRanksOfPlayer(string $player_uuid): Generator
    {
        return yield from $this->lock(function () use ($player_uuid): Generator {
            $this->db->executeSelect('ranked.player_ranks.list_ranks', [
                'player_uuid' => $player_uuid,
            ], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    public function listPlayersOfRank(string $rank_id): Generator
    {
        return yield from $this->lock(function () use ($rank_id): Generator {
            $this->db->executeSelect('ranked.player_ranks.list_players', [
                'rank_id' => $rank_id,
            ], yield Await::RESOLVE, yield Await::REJECT);

            return yield Await::ONCE;
        });
    }

    /**
     * @param Closure(): Generator $closure
     */
    private function lock(Closure $closure): Generator
    {
        if (null !== $this->sqliteLock) {
            return yield from $this->sqliteLock->run($closure());
        }

        return yield from $closure();
    }
}
