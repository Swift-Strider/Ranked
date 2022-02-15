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

use DiamondStrider1\DiamondDatas\attributes\StringType;
use DiamondStrider1\DiamondDatas\ConfigContext;
use DiamondStrider1\DiamondDatas\ConfigException;
use DiamondStrider1\DiamondDatas\metadata\IValidationProvider;
use DiamondStrider1\Ranked\config\IConfig;

class Config implements IConfig, IValidationProvider
{
    public const ALL_LANGS = [
        'en_US' => true,
    ];

    #[StringType(
        config_key: 'lang',
        description: 'The language for the plugin. Supports: "en_US"'
    )]
    public string $language;

    public static function createDefault(): self
    {
        $self = new self();
        $self->language = 'en_US';

        return $self;
    }

    public function validate(ConfigContext $context): void
    {
        $langExists = self::ALL_LANGS[$this->language] ?? false;
        if (!$langExists) {
            throw new ConfigException(
                "The `lang` \"{$this->language}\" is not supported. ".
                    'Use '.implode(', ', array_keys(self::ALL_LANGS))
            );
        }
    }
}
