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

use ArrayIterator;
use AssertionError;
use DiamondStrider1\Ranked\command\attributes\CommandSettings;
use DiamondStrider1\Ranked\command\parameters\CommandParameter;
use DiamondStrider1\Ranked\command\parameters\ParameterRegister;
use DiamondStrider1\Ranked\form\CustomForm;
use DiamondStrider1\Ranked\Loader;
use InvalidArgumentException;
use Iterator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use ReflectionMethod;
use ReflectionNamedType;

class CommandOverload extends Command implements PluginOwned
{
    public const TYPE_ALL = 0;
    public const TYPE_PLAYER = 1;

    private int $type;

    /** @var CommandParameter[] */
    private array $params = [];

    public function __construct(
        CommandSettings $s,
        private ReflectionMethod $method,
        private OverloadedCommand $owner,
    ) {
        parent::__construct($s->getName(), $s->getDescription(), $s->getUsageMessage(), $s->getAliases());
        $this->setPermission($s->getPermission());

        $rParams = $method->getParameters();
        if (0 === \count($rParams)) {
            throw new InvalidArgumentException('$method takes no parameters!');
        }
        $first = $rParams[0]->getType();
        if (!$first instanceof ReflectionNamedType) {
            throw new InvalidArgumentException("\$method's first parameter does not take a single named type!");
        }
        $this->type = match ($first->getName()) {
            Player::class => self::TYPE_PLAYER,
            CommandSender::class => self::TYPE_ALL,
            default => throw new InvalidArgumentException("\$method's first parameter is not of type Player or CommandSender!"),
        };

        $defaultUsage = '';
        foreach ($rParams as $i => $p) {
            if (0 === $i) {
                continue;
            }
            $rType = $p->getType();
            if (!$rType instanceof ReflectionNamedType) {
                throw new InvalidArgumentException("\$method's parameter at index {$i} is not a single named type!");
            }
            $param = ParameterRegister::get($rType->getName());
            if (null === $param) {
                throw new InvalidArgumentException("\$method's parameter at index {$i} is of an unregistered type!");
            }

            $pName = strtolower($p->getName());
            $pType = $param->getUsageType();
            $defaultUsage .= "<{$pName}: {$pType}> ";
            $this->params[] = $param;
        }

        if (null === $s->getUsageMessage() && '' !== $defaultUsage) {
            $this->setUsage(trim($defaultUsage));
        }
    }

    /**
     * @param string[] $args
     */
    public function execute(CommandSender $sender, string $label, array $args): void
    {
        if (!$this->testPermissionSilent($sender)) {
            return;
        }
        if (self::TYPE_PLAYER === $this->type && !$sender instanceof Player) {
            $sender->sendMessage('§4You must run this command as a player!');

            return;
        }

        $args = new CommandArgs($args, $label);
        $validParams = [$sender];
        $remainingParams = new ArrayIterator($this->params);

        try {
            while ($remainingParams->valid()) {
                $p = $remainingParams->current();
                $args->prepare();
                $validParams[] = $p->get($args);
                $remainingParams->next();
            }
        } catch (ValidationException $e) {
            if (!$sender instanceof Player) {
                $sender->sendMessage($e->getMessage());

                return;
            }

            $this->promptPlayer($sender, $label, $validParams, $remainingParams, $e);

            return;
        }
        $this->method->invokeArgs($this->owner, [$sender] + $validParams);
    }

    public function getOwningPlugin(): Loader
    {
        return Loader::get();
    }

    /**
     * @param array<int, mixed>          $validParams
     * @param Iterator<CommandParameter> $remainingParams
     */
    private function promptPlayer(Player $sender, string $label, array $validParams, Iterator $remainingParams, ValidationException $e): void
    {
        $error = $e->getMessage();
        CustomForm::create()
            ->title("Running \"/{$label}\"")
            ->label('§c'.$e->getMessage())
            ->input('Fill in missing arguments here.')
            ->queryPlayer($sender)
            ->onCompletion(function ($response) use ($sender, $label, $validParams, $remainingParams, $error) {
                $value = $response[1] ?? null;
                if (null === $value) {
                    $sender->sendMessage($error);

                    return;
                }
                if (!\is_string($value)) {
                    throw new AssertionError('CustomForm should have ensured that $value is a string!');
                }

                $args = new CommandArgs(explode(' ', $value), $label);

                try {
                    while ($remainingParams->valid()) {
                        $p = $remainingParams->current();
                        $args->prepare();
                        $validParams[] = $p->get($args);
                        $remainingParams->next();
                    }
                } catch (ValidationException $e) {
                    if (!$sender instanceof Player) {
                        $sender->sendMessage($e->getMessage());

                        return;
                    }

                    $this->promptPlayer($sender, $label, $validParams, $remainingParams, $e);

                    return;
                }
                $this->method->invokeArgs($this->owner, [$sender] + $validParams);
            }, function (): void {})
        ;
    }
}
