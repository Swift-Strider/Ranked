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

namespace DiamondStrider1\Ranked\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class MountedCommand extends Command implements PluginOwned
{
    /** @var array<string, CommandOverload> */
    private array $overloadMap;

    public function __construct(
        string $name,
        private CommandBase $base,
        private Plugin $plugin,
    ) {
        parent::__construct($name, $base->getCommandGroup()->getDescription());

        $this->setPermission($base->getCommandGroup()->getPermission());
        foreach ($this->base->getOverloads() as $overload) {
            $this->overloadMap[$overload->getName()] = $overload;
        }
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if (!$this->testPermissionSilent($sender)) {
            return;
        }

        $overloadName = array_shift($args);
        if (null === $overloadName) {
            $sender->sendMessage('§cThat subcommand does not exist!');
            $sender->sendMessage('§cTry: '.implode(', ', array_keys($this->overloadMap)));

            return;
        }

        $overload = $this->overloadMap[$overloadName];
        $overload->execute($sender, "{$label} {$overloadName}", $args);
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }
}
