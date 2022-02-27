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

use DomainException;
use Generator;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use SOFe\AwaitGenerator\Await;

final class MenuForm
{
    use FormTrait;
    private string $content;

    /** @var array<int, array{text: string, image?: array{type: string, data: string}}> */
    private array $buttons = [];

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param 'path'|'url' $iconType
     */
    public function button(string $text, ?string $iconType = null, ?string $iconLocation = null): self
    {
        $button = ['text' => $text];
        if (null !== $iconType && null !== $iconLocation) {
            $button['image'] = [
                'type' => $iconType,
                'data' => $iconLocation,
            ];
        }
        $this->buttons[] = $button;

        return $this;
    }

    /**
     * @phpstan-return Promise<int|null>
     */
    public function sendPromise(Player $player): Promise
    {
        if (!isset($this->title) || !isset($this->content)) {
            throw new DomainException('Some required properties have not been set!');
        }

        $resolver = new PromiseResolver();
        $formData = [
            'type' => 'form',
            'title' => $this->title,
            'content' => $this->content,
            'buttons' => $this->buttons,
        ];

        $validator = function ($data) {
            if (!\is_int($data) && null !== $data) {
                throw new FormValidationException('Expected a response of type int or null, got type '.\gettype($data).' instead!');
            }
        };

        $form = new InternalForm($formData, $resolver, $validator);

        $player->sendForm($form);

        return $resolver->getPromise();
    }

    /**
     * @phpstan-return Generator<mixed, AwaitValue, mixed, int|null>
     */
    public function sendGenerator(Player $player): Generator
    {
        $this->sendPromise($player)
            ->onCompletion(
                yield Await::RESOLVE,
                yield Await::REJECT
            )
        ;

        return yield Await::ONCE;
    }
}
