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

namespace DiamondStrider1\Ranked\language;

use AssertionError;
use DiamondStrider1\Ranked\config\Manager as ConfigManager;
use DiamondStrider1\Ranked\Loader;
use DiamondStrider1\Ranked\manager\IManager;
use DiamondStrider1\Ranked\manager\ManagerTrait;
use Generator;

class Manager implements IManager
{
    use ManagerTrait;

    private Loader $plugin;
    private ConfigManager $configManager;
    private Config $config;
    private Language $language;

    public function getLang(): Language
    {
        return $this->language;
    }

    private function onLoad(): Generator
    {
        false && yield;

        $this->config = $this->configManager->getConfig()->language;
        $language = $this->config->language;
        $resource = $this->plugin->getResource("langs/{$language}.ini");
        if (null === $resource) {
            throw new AssertionError("Cannot open language file `langs/{$language}.ini`");
        }
        $this->language = new Language($resource);
    }
}
