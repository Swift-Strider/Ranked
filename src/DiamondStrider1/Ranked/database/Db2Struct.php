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

use DiamondStrider1\Ranked\struct\Rank;
use DiamondStrider1\Ranked\struct\RankedPlayer;
use DiamondStrider1\Ranked\struct\RankInheritance;
use DiamondStrider1\Ranked\struct\RankInstance;

/**
 * Utility function for creating structs from the database.
 */
final class Db2Struct
{
    /**
     * @param array<string, mixed> $row
     */
    public static function RankInstance(array $row): RankInstance
    {
        return new RankInstance(
            new Rank($row['rank_id'], $row['rank_name']),
            new RankedPlayer($row['player_uuid'], $row['username'], $row['display_name']),
            $row['expiration_date']
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function RankInheritance(array $row): RankInheritance
    {
        return new RankInheritance(
            $row['child_id'],
            $row['parent_id']
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function Rank(array $row): Rank
    {
        return new Rank(
            $row['id'],
            $row['name']
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function RankedPlayer(array $row): RankedPlayer
    {
        return new RankedPlayer(
            $row['uuid'],
            $row['username'],
            $row['display_name']
        );
    }
}
