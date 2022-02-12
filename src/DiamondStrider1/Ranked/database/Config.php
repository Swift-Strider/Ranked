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

use DiamondStrider1\DiamondDatas\attributes\IntType;
use DiamondStrider1\DiamondDatas\attributes\ObjectType;
use DiamondStrider1\DiamondDatas\attributes\StringType;
use DiamondStrider1\DiamondDatas\ConfigContext;
use DiamondStrider1\DiamondDatas\ConfigException;
use DiamondStrider1\DiamondDatas\metadata\IValidationProvider;
use DiamondStrider1\Ranked\config\IConfig;

class Config implements IConfig, IValidationProvider
{
    #[StringType('type', <<<'EOT'
        What SQL type to use.
        Can be MySQL (external) or SQLite (uses a local file).
        EOT)]
    public string $type;
    #[ObjectType(SQLiteSettings::class, 'sqlite', <<<'EOT'
        Edit these settings only if you choose "sqlite".
        EOT)]
    public SQLiteSettings $sqlite;
    #[ObjectType(MySQLSettings::class, 'mysql', <<<'EOT'
        Edit these settings only if you choose "mysql".
        EOT)]
    public MySQLSettings $mysql;
    #[IntType('worker-limit', <<<'EOT'
        The maximum number of simultaneous SQL queries
        Recommended: 1 for sqlite, 2 for MySQL.
        You may want to further increase this value if
        your MySQL connection is very slow
        EOT)]
    public int $workerLimit;

    public static function createDefault(): self
    {
        $self = new self();
        $self->type = 'sqlite';
        $self->sqlite = new SQLiteSettings('sqlite.db');
        $self->mysql = new MySQLSettings('127.0.0.1', 'root', '', '');
        $self->workerLimit = 1;

        return $self;
    }

    public function validate(ConfigContext $context): void
    {
        if ('sqlite' !== $this->type && 'mysql' !== $this->type) {
            throw new ConfigException("Database 'type' must be 'sqlite' or 'mysql'!", $context);
        }
        if ($this->workerLimit < 1) {
            throw new ConfigException("Database 'worker-limit' must be AT LEAST 1!", $context);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function convertToArray(): array
    {
        return [
            'type' => $this->type,
            'sqlite' => [
                'file' => $this->sqlite->file,
            ],
            'mysql' => [
                'host' => $this->mysql->host,
                'username' => $this->mysql->username,
                'password' => $this->mysql->password,
                'schema' => $this->mysql->schema,
            ],
            'worker-limit' => $this->workerLimit,
        ];
    }
}
