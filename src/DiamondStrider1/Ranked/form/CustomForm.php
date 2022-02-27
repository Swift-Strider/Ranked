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

use AssertionError;
use DomainException;
use Generator;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use SOFe\AwaitGenerator\Await;

final class CustomForm
{
    use FormTrait;

    /** @var array<int, array<string, mixed>> */
    private array $content;

    public function label(string $text): self
    {
        $this->content[] = [
            'type' => 'label',
            'text' => $text,
        ];

        return $this;
    }

    public function input(string $text, string $placeholder = '', string $default = ''): self
    {
        $this->content[] = [
            'type' => 'input',
            'text' => $text,
            'placeholder' => $placeholder,
            'default' => $default,
        ];

        return $this;
    }

    /**
     * @param array<int, string> $options
     */
    public function dropdown(string $text, array $options, int $defaultOption = 0): self
    {
        $this->content[] = [
            'type' => 'dropdown',
            'text' => $text,
            'options' => $options,
            'default' => $defaultOption,
        ];

        return $this;
    }

    /**
     * @param array<int, string> $steps
     */
    public function step_slider(string $text, array $steps, int $defaultOption = 0): self
    {
        $this->content[] = [
            'type' => 'step_slider',
            'text' => $text,
            'steps' => $steps,
            'default' => $defaultOption,
        ];

        return $this;
    }

    public function slider(string $text, float $min, float $max, float $step = 1.0, ?float $default = null): self
    {
        $this->content[] = [
            'type' => 'slider',
            'text' => $text,
            'min' => $min,
            'max' => $max,
            'step' => $step,
            'default' => $default ?? $min,
        ];

        return $this;
    }

    public function toggle(string $text, bool $default = false): self
    {
        $this->content[] = [
            'type' => 'toggle',
            'text' => $text,
            'default' => $default,
        ];

        return $this;
    }

    /**
     * @phpstan-return Promise<null|array<int, mixed>>
     */
    public function sendPromise(Player $player): Promise
    {
        if (!isset($this->title) || !isset($this->content)) {
            throw new DomainException('Some required properties have not been set!');
        }

        $resolver = new PromiseResolver();
        $formData = [
            'type' => 'custom_form',
            'title' => $this->title,
            'content' => $this->content,
        ];

        $validator = function ($data) {
            if (null === $data) {
                return;
            }
            if (!\is_array($data)) {
                throw new FormValidationException('Expected a response of type int or null, got type '.\gettype($data).' instead!');
            }
            foreach ($this->content as $i => $el) {
                if (!\array_key_exists($i, $data)) {
                    throw new FormValidationException("Expected response to have a value at index {$i}, but nothing was given!");
                }
                $value = $data[$i];
                $isValid = match ($el['type']) {
                    'label' => null === $value,
                    'input' => \is_string($value),
                    // @phpstan-ignore-next-line Cannot access offset int on mixed.
                    'dropdown' => \is_int($value) && isset($el['options'][$value]),
                    // @phpstan-ignore-next-line Cannot access offset int on mixed.
                    'step_slider' => \is_int($value) && isset($el['steps'][$value]),
                    'slider' => (\is_float($value) || \is_int($value)) && $value >= $el['min'] && $value <= $el['max'],
                    'toggle' => \is_bool($value),
                    default => throw new AssertionError('The element type '.$el['type'].' should not occur!')
                };
                if (!$isValid) {
                    throw new FormValidationException('Expected valid data for "'.$el['type'].'" element!');
                }
            }
        };

        $form = new InternalForm($formData, $resolver, $validator);

        $player->sendForm($form);

        return $resolver->getPromise();
    }

    /**
     * @phpstan-return Generator<mixed, AwaitValue, mixed, null|array<int, mixed>>
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
