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

namespace DiamondStrider1\Ranked\manager;

use Generator;

/**
 * This trait implements Manager.
 */
trait ManagerTrait
{
    private static self $instance;
    private static ManagerLoadFailedException $failed;

    /**
     * @throws ManagerLoadFailedException
     */
    public static function get(): Generator
    {
        if (isset(self::$failed)) {
            throw self::$failed;
        }

        if (!isset(self::$instance)) {
            self::$instance = new self();

            try {
                yield from self::$instance->onLoad();
            } catch (ManagerLoadFailedException $e) {
                throw self::$failed = $e;
            }
        }

        return self::$instance;
    }

    public function dispose(): void
    {
        // noop
    }

    /**
     * @throws ManagerLoadFailedException
     *
     * @return never
     */
    private function fail(): void
    {
        throw new ManagerLoadFailedException(static::class.' failed to load!');
    }

    /**
     * @throws ManagerLoadFailedException
     */
    abstract private function onLoad(): Generator;
}
