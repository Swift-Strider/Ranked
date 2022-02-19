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

namespace DiamondStrider1\Ranked\struct;

class PermissionSet
{
    /**
     * @var array<int, string> list of permissions
     */
    private array $permissions;

    /**
     * @var array<string, bool> maps permissions to `true` for quick indexing
     */
    private array $permissionSet;

    /**
     * @param array<int, string> $permissions
     */
    public function __construct(
        array $permissions,
    ) {
        $this->permissions = array_unique($permissions);
        $this->permissionSet = [];
        foreach ($permissions as $perm) {
            $this->permissionSet[$perm] = true;
        }
    }

    /**
     * @return array<int, string>
     */
    public function getAll(): array
    {
        return $this->permissions;
    }

    /**
     * Returns permissions as keys to an array.
     * All keys will map to a `true` boolean.
     *
     * @return array<string, bool>
     */
    public function getSet(): array
    {
        return $this->permissionSet;
    }

    /**
     * Adds the permission set with another and returns a new set.
     */
    public function add(self $other): self
    {
        $perms = array_unique(array_merge($this->getAll(), $other->getAll()));

        return new self($perms);
    }
}
