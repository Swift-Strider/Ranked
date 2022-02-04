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

namespace DiamondStrider1\Ranked\ranks;

use DiamondStrider1\Ranked\command\attributes\CommandSettings;
use DiamondStrider1\Ranked\command\CommandBase;
use pocketmine\command\CommandSender;

class RankCommand extends CommandBase
{
    #[CommandSettings(
        name: 'rank_add',
        permission: 'ranked.ranks.command.add',
        description: 'Add a new rank to your server!',
        usageMessage: '/rank_add <name: string>',
    )]
    public function addRank(CommandSender $sender, string $name): void
    {
    }

    #[CommandSettings(
        name: 'rank_remove',
        permission: 'ranked.ranks.command.remove',
        description: 'Remove a rank from your server!',
        usageMessage: '/rank_remove <name: string>',
    )]
    public function removeRank(CommandSender $sender, string $name): void
    {
    }
}
