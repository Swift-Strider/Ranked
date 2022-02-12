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

namespace DiamondStrider1\Ranked;

use DiamondStrider1\Ranked;
use DiamondStrider1\Ranked\manager\ManagerLoadFailedException;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase
{
    private static self $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        try {
            Ranked\config\Manager::get();
            Ranked\database\Manager::get();
            Ranked\ranks\Manager::get();
        } catch (ManagerLoadFailedException $e) {
            $this->getLogger()->critical('Detected Manager Failure: '.$e->getMessage());
            $this->getServer()->shutdown();
        }
    }

    public function onDisable(): void
    {
        Ranked\config\Manager::get()->dispose();
        Ranked\database\Manager::get()->dispose();
        Ranked\ranks\Manager::get()->dispose();
    }

    public static function get(): self
    {
        return self::$instance;
    }
}
