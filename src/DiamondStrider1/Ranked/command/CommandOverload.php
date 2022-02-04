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

use DiamondStrider1\Ranked\command\parameters\CommandParameter;
use DiamondStrider1\Ranked\command\parameters\ParameterRegister;
use DiamondStrider1\Ranked\Loader;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
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

    /**
     * @param string[] $aliases
     */
    public function __construct(
        string $name,
        ?string $permission,
        Translatable|string $description,
        Translatable|string|null $usageMessage = null,
        array $aliases,
        private ReflectionMethod $method,
        private CommandBase $owner,
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission($permission);

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
            $this->params[] = $param;
        }
    }

    /**
     * @param string[] $args
     */
    public function execute(CommandSender $sender, string $label, array $args): void
    {
        if (self::TYPE_PLAYER === $this->type && !$sender instanceof Player) {
            $sender->sendMessage('§4You must run this command as a player!');

            return;
        }

        $args = new CommandArgs($args);
        $params = [$sender];
        foreach ($this->params as $p) {
            try {
                $params[] = $p->get($args);
            } catch (ValidationException $e) {
                $sender->sendMessage($e->getMessage());

                return;
            }
        }
        $this->method->invokeArgs($this->owner, [$sender] + $params);
    }

    public function getOwningPlugin(): Loader
    {
        return Loader::get();
    }
}
