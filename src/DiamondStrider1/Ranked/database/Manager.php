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

use DiamondStrider1\Ranked\config\Manager as ConfigManager;
use DiamondStrider1\Ranked\Loader;
use DiamondStrider1\Ranked\manager\IManager;
use DiamondStrider1\Ranked\manager\ManagerTrait;
use Generator;
use Logger;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;

class Manager implements IManager
{
    use ManagerTrait;

    private Loader $plugin;
    private Logger $logger;
    private ConfigManager $configManager;
    private Config $config;
    private DataConnector $database;
    private QueryRunner $queryRunner;

    public function onLoad(): Generator
    {
        $this->config = $this->configManager->getConfig()->database;

        try {
            $this->database = libasynql::create(
                $this->plugin,
                $this->config->convertToArray(),
                [
                    'sqlite' => 'db_stmts/sqlite.sql',
                    'mysql' => 'db_stmts/mysql.sql',
                ],
                false
            );
        } catch (SqlError $e) {
            if (SqlError::STAGE_CONNECT !== $e->getStage()) {
                throw $e;
            }
            $this->logger->emergency($e->getErrorMessage());
            $this->fail();
        }

        if ($this->config->logQueries) {
            $this->database->setLogger($this->logger);
        }

        $this->queryRunner = new QueryRunner($this->database);

        yield from $this->queryRunner->initRanks();
        yield from $this->queryRunner->initRankpermissions();
        yield from $this->queryRunner->initPlayers();
        yield from $this->queryRunner->initRankPlayers();
    }

    public function getQueryRunner(): QueryRunner
    {
        return $this->queryRunner;
    }

    public function dispose(): void
    {
        if (isset($this->database)) {
            $this->database->close();
        }
    }
}
