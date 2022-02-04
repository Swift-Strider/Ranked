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

class CommandArgs
{
    private int $start = 0;
    private int $index = 0;

    /**
     * @param string[] $args
     */
    public function __construct(
        private array $args,
        private string $label,
    ) {
    }

    public function poll(): ?string
    {
        return $this->args[$this->index] ?? null;
    }

    public function take(): ?string
    {
        return $this->args[$this->index++] ?? null;
    }

    public function prepare(): void
    {
        $this->start = $this->index;
    }

    /**
     * @return never
     */
    public function fail(string $message): void
    {
        $begin = implode(' ', \array_slice(
            $this->args,
            0,
            $this->start
        ));
        $middle = implode(' ', \array_slice(
            $this->args,
            $this->start,
            $this->index - $this->start
        ));
        $end = implode(' ', \array_slice(
            $this->args,
            $this->index
        ));

        $newMessage = "§c/{$this->label} ";
        if (!empty($begin)) {
            $newMessage .= "{$begin} ";
        }
        $newMessage .= '§l>>>§r§c ';
        if (!empty($middle)) {
            $newMessage .= "{$middle} ";
        }
        $newMessage .= '§l<<<§r§c';
        if (!empty($end)) {
            $newMessage .= " {$end}";
        }
        $newMessage .= "\n§c{$message}";

        throw new ValidationException($newMessage);
    }
}
