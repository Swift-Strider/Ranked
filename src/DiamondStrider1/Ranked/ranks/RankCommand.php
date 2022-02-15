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

use DiamondStrider1\Ranked\command\attributes\CommandGroup;
use DiamondStrider1\Ranked\command\attributes\CommandSettings;
use DiamondStrider1\Ranked\command\CommandBase;
use pocketmine\command\CommandSender;

#[CommandGroup(
    description: 'Manage ranks on your server!',
    permission: 'ranked.ranks.command'
)]
class RankCommand extends CommandBase
{
    #[CommandSettings(
        name: 'add',
        permission: 'ranked.ranks.command.add',
        description: 'Add a new rank to your server!',
    )]
    public function addRank(CommandSender $sender, string $name): void
    {
        $sender->sendMessage("add: {$name}");
    }

    #[CommandSettings(
        name: 'remove',
        permission: 'ranked.ranks.command.remove',
        description: 'Remove a rank from your server!',
    )]
    public function removeRank(CommandSender $sender, string $name): void
    {
        $sender->sendMessage("remove: {$name}");
    }
}
