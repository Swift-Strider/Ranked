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

namespace DiamondStrider1\Ranked\form;

use Closure;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\promise\PromiseResolver;

/**
 * @template TValue
 */
final class PromiseForm implements Form
{
    /**
     * @param array<string, mixed> $formData
     * @phpstan-param PromiseResolver<TValue> $resolver
     * @phpstan-param Closure(mixed $data): void $validator
     */
    public function __construct(
        private array $formData,
        private PromiseResolver $resolver,
        private Closure $validator,
    ) {
    }

    public function handleResponse(Player $player, $data): void
    {
        try {
            ($this->validator)($data);
        } catch (FormValidationException $e) {
            $this->resolver->reject();

            throw $e;
        }

        // @phpstan-ignore-next-line $this->validator should ensure correct data type
        $this->resolver->resolve($data);
    }

    public function jsonSerialize(): mixed
    {
        return $this->formData;
    }
}
