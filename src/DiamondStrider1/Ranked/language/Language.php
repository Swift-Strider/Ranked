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

class Language
{
    /** @var string[] */
    private array $texts = [];

    /**
     * @param resource $langFile
     */
    public function __construct($langFile)
    {
        while (($line = fgets($langFile)) !== false) {
            $explosion = explode('=', rtrim($line));
            $key = $explosion[0] ?? null;
            $value = $explosion[1] ?? null;
            if (null === $key || null === $value) {
                continue;
            }
            $this->texts[$key] = $value;
        }
    }

    /**
     * @param array<string, string> $params
     */
    private function getRaw(string $key, array $params): string
    {
        $text = $this->texts[$key] ?? null;
        if (null === $text) {
            return $key;
        }
        foreach ($params as $name => $param) {
            $text = str_replace("%{$name}%", $param, $text);
        }

        return $text;
    }
}
