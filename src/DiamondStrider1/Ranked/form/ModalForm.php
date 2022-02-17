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
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

final class ModalForm
{
    private string $title;
    private string $content;
    private string $yesText = 'gui.yes';
    private string $noText = 'gui.no';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function yesText(string $yesText): self
    {
        $this->yesText = $yesText;

        return $this;
    }

    public function noText(string $noText): self
    {
        $this->noText = $noText;

        return $this;
    }

    /**
     * @phpstan-return Promise<bool>
     */
    public function queryPlayer(Player $player): Promise
    {
        if (!isset($this->title) || !isset($this->content)) {
            throw new DomainException('Some required properties have not been set!');
        }

        $resolver = new PromiseResolver();
        $formData = [
            'type' => 'modal',
            'title' => $this->title,
            'content' => $this->content,
            'button1' => $this->yesText,
            'button2' => $this->noText,
        ];

        $validator = function ($data) {
            if (!\is_bool($data)) {
                throw new FormValidationException('Expected a response of type bool, got type '.\gettype($data).' instead!');
            }
        };

        $form = new PromiseForm($formData, $resolver, $validator);

        $player->sendForm($form);

        return $resolver->getPromise();
    }
}
