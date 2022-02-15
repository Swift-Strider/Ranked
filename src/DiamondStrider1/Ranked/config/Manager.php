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

namespace DiamondStrider1\Ranked\config;

use DiamondStrider1\DiamondDatas\ConfigException;
use DiamondStrider1\DiamondDatas\NeoConfig;
use DiamondStrider1\Ranked\Loader;
use DiamondStrider1\Ranked\manager\IManager;
use DiamondStrider1\Ranked\manager\ManagerTrait;
use Generator;
use Logger;

class Manager implements IManager
{
    use ManagerTrait;

    private Loader $plugin;
    private Logger $logger;

    /** @phpstan-var NeoConfig<Config> */
    private NeoConfig $neoConfig;

    public function onLoad(): Generator
    {
        false && yield;

        $filename = $this->plugin->getDataFolder().'config.yml';
        $this->neoConfig = new NeoConfig($filename, Config::class);

        try {
            $this->getConfig();
        } catch (ConfigException $e) {
            $this->logger->emergency("Error Loading Config\n".$e->getMessage());
            $this->fail();
        }
    }

    public function getConfig(): Config
    {
        return $this->neoConfig->getObject();
    }

    public function setConfig(Config $config): void
    {
        $this->neoConfig->setObject($config);
    }
}
