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

use DiamondStrider1\DiamondDatas\attributes\ObjectType;
use DiamondStrider1\DiamondDatas\metadata\IDefaultProvider;
use DiamondStrider1\Ranked\database\Config as DatabaseConfig;
use DiamondStrider1\Ranked\language\Config as LanguageConfig;

class Config implements IDefaultProvider
{
    #[ObjectType(DatabaseConfig::class, 'database', 'Database Configuration')]
    public DatabaseConfig $database;
    #[ObjectType(LanguageConfig::class, 'language', 'Language Configuration')]
    public LanguageConfig $language;

    public static function getDefaults(): array
    {
        return [
            'database' => DatabaseConfig::createDefault(),
            'language' => LanguageConfig::createDefault(),
        ];
    }
}
