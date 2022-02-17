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

use AssertionError;
use DiamondStrider1\Ranked\command\attributes\CommandGroup;
use DiamondStrider1\Ranked\command\attributes\CommandSettings;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

abstract class CommandBase
{
    /** @var CommandOverload[] */
    private array $overloads;

    public function getCommandGroup(): CommandGroup
    {
        $group = (new ReflectionClass(static::class))->getAttributes(CommandGroup::class)[0] ?? null;
        if (null === $group) {
            throw new AssertionError(static::class.' is missing a CommandGroup attribute');
        }

        return $group->newInstance();
    }

    /** @return CommandOverload[] */
    public function getOverloads(): array
    {
        if (isset($this->overloads)) {
            return $this->overloads;
        }

        $rMethods = (new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC);
        $overloads = [];
        foreach ($rMethods as $m) {
            $rAttr = $m->getAttributes(CommandSettings::class)[0] ?? null;
            if (null === $rAttr) {
                continue;
            }
            $s = $rAttr->newInstance();

            try {
                $overloads[] = new CommandOverload(
                    $s,
                    $m,
                    $this
                );
            } catch (InvalidArgumentException) {
                // noop
            }
        }

        return $this->overloads = $overloads;
    }
}
