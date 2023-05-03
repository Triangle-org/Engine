<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace support\telegram\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use support\telegram\Answers\AnswerBus;
use support\telegram\Api;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\Objects\MessageEntity;
use support\telegram\Objects\Update;
use support\telegram\Traits\Singleton;

/**
 * Class CommandBus.
 */
class CommandBus extends AnswerBus
{
    use Singleton;

    /**
     * @var array<string, Command> Holds all commands. Keys are command names (without leading slashes).
     */
    protected $commands = [];

    /**
     * @var array<string, Command> Holds all commands' aliases. Keys are command names (without leading slashes).
     */
    protected $commandAliases = [];

    /**
     * Instantiate Command Bus.
     *
     * @param Api|null $telegram
     */
    public function __construct(Api $telegram = null)
    {
        $this->telegram = $telegram;
    }

    /**
     * Returns the list of commands.
     *
     * @return array<string, Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Add a list of commands.
     *
     * @param list<CommandInterface|class-string<CommandInterface>> $commands
     *
     * @return CommandBus
     * @throws TelegramSDKException
     */
    public function addCommands(array $commands): self
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        return $this;
    }

    /**
     * Add a command to the commands list.
     *
     * @param CommandInterface|class-string<CommandInterface> $command Either an object or fully qualified class name (FQCN) of the command class.
     *
     * @return CommandBus
     * @throws TelegramSDKException
     *
     */
    public function addCommand($command): self
    {
        $command = $this->resolveCommand($command);

        /*
         * At this stage we definitely have a proper command to use.
         *
         * @var Command $command
         */
        $this->commands[$command->getName()] = $command;

        $aliases = $command->getAliases();

        if (empty($aliases)) {
            return $this;
        }

        foreach ($command->getAliases() as $alias) {
            $this->checkForConflicts($command, $alias);

            $this->commandAliases[$alias] = $command;
        }

        return $this;
    }

    /**
     * Remove a command from the list.
     *
     * @param string $name Command's name without leading slash
     *
     * @return CommandBus
     */
    public function removeCommand($name): self
    {
        unset($this->commands[$name]);

        return $this;
    }

    /**
     * Removes a list of commands.
     *
     * @param list<string> $names Command names
     *
     * @return CommandBus
     */
    public function removeCommands(array $names): self
    {
        foreach ($names as $name) {
            $this->removeCommand($name);
        }

        return $this;
    }

    /**
     * Parse a Command for a Match.
     *
     * @param string $text Command name with a leading slash
     * @param int $offset
     * @param int $length
     *
     * @return string Telegram command name (without leading slash)
     */
    public function parseCommand($text, $offset, $length): string
    {
        if (trim($text) === '') {
            throw new InvalidArgumentException('Message is empty, Cannot parse for command');
        }

        // remove leading slash
        $command = substr(
            $text,
            $offset + 1,
            $length - 1
        );

        // When in group - Ex: /command@MyBot
        if (Str::contains($command, '@') && Str::endsWith($command, ['bot', 'Bot'])) {
            $command = explode('@', $command);
            $command = $command[0];
        }

        return $command;
    }

    /**
     * Handles Inbound Messages and Executes Appropriate Command.
     *
     * @param Update $update
     *
     * @return Update
     */
    protected function handler(Update $update): Update
    {
        $message = $update->getMessage();

        if ($message->has('entities')) {
            $this->parseCommandsIn($message)
                ->each(function ($botCommandEntity) use ($update) {
                    $botCommandAsArray = $botCommandEntity instanceof MessageEntity
                        ? $botCommandEntity->all()
                        : $botCommandEntity;
                    $this->process($botCommandAsArray, $update);
                });
        }

        return $update;
    }

    /**
     * Returns all bot_commands detected in the update.
     *
     * @param \support\telegram\Objects\Message|Collection $message
     *
     * @return Collection<int, MessageEntity>
     */
    protected function parseCommandsIn(Collection $message): Collection
    {
        return Collection::wrap($message->get('entities'))
            ->filter(function (MessageEntity $entity) {
                return $entity->type === 'bot_command';
            });
    }

    /**
     * Execute a bot command from the update text.
     *
     * @param array<string, mixed> $entity {@see \support\telegram\Objects\MessageEntity} object attributes.
     * @param Update $update
     */
    protected function process($entity, Update $update)
    {
        $command = $this->parseCommand(
            $update->getMessage()->text,
            $entity['offset'],
            $entity['length']
        );

        $this->execute($command, $update, $entity);
    }

    /**
     * Execute the command.
     *
     * @param string $name Telegram command name without leading slash
     * @param Update $update
     * @param array<string, mixed> $entity
     *
     * @return mixed
     */
    protected function execute(string $name, Update $update, array $entity)
    {
        $command = $this->commands[$name] ??
            $this->commandAliases[$name] ??
            $this->commands['help'] ??
            collect($this->commands)->filter(function ($command) use ($name) {
                return $command instanceof $name;
            })->first() ?? null;

        return $command ? $command->make($this->telegram, $update, $entity) : false;
    }

    /**
     * @param CommandInterface|class-string<CommandInterface> $command
     *
     * @return CommandInterface
     * @throws TelegramSDKException
     */
    private function resolveCommand($command): CommandInterface
    {
        if (!is_a($command, CommandInterface::class, true)) {
            throw new TelegramSDKException(
                sprintf(
                    'Command class "%s" should be an instance of "%s"',
                    is_object($command) ? get_class($command) : $command,
                    CommandInterface::class
                )
            );
        }

        if (is_object($command)) {
            $commandInstance = $command;
        } else {
            $commandInstance = $this->telegram->hasContainer()
                ? $this->buildDependencyInjectedAnswer($command)
                : new $command();
        }

        if ($commandInstance instanceof Command && $this->telegram) {
            $commandInstance->setTelegram($this->getTelegram());
        }

        return $commandInstance;
    }

    /**
     * @param CommandInterface $command
     * @param string $alias
     *
     * @return void
     * @throws TelegramSDKException
     */
    private function checkForConflicts($command, $alias)
    {
        if (isset($this->commands[$alias])) {
            throw new TelegramSDKException(
                sprintf(
                    '[Error] Alias [%s] conflicts with command name of "%s" try with another name or remove this alias from the list.',
                    $alias,
                    get_class($command)
                )
            );
        }

        if (isset($this->commandAliases[$alias])) {
            throw new TelegramSDKException(
                sprintf(
                    '[Error] Alias [%s] conflicts with another command\'s alias list: "%s", try with another name or remove this alias from the list.',
                    $alias,
                    get_class($command)
                )
            );
        }
    }
}
