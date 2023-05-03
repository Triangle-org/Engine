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

namespace Triangle\Engine\Console\Descriptor;

use Triangle\Engine\Console\Application;
use Triangle\Engine\Console\Command\Command;
use Triangle\Engine\Console\Exception\CommandNotFoundException;

/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class ApplicationDescription
{
    public const GLOBAL_NAMESPACE = '_global';

    private $application;
    private $namespace;
    private $showHidden;

    /**
     * @var array
     */
    private $namespaces;

    /**
     * @var array<string, Command>
     */
    private $commands;

    /**
     * @var array<string, Command>
     */
    private $aliases;

    public function __construct(Application $application, string $namespace = null, bool $showHidden = false)
    {
        $this->application = $application;
        $this->namespace = $namespace;
        $this->showHidden = $showHidden;
    }

    public function getNamespaces(): array
    {
        if (null === $this->namespaces) {
            $this->inspectApplication();
        }

        return $this->namespaces;
    }

    /**
     * @return Command[]
     */
    public function getCommands(): array
    {
        if (null === $this->commands) {
            $this->inspectApplication();
        }

        return $this->commands;
    }

    /**
     * @throws CommandNotFoundException
     */
    public function getCommand(string $name): Command
    {
        if (!isset($this->commands[$name]) && !isset($this->aliases[$name])) {
            throw new CommandNotFoundException(sprintf('Команда "%s" не существует.', $name));
        }

        return $this->commands[$name] ?? $this->aliases[$name];
    }

    private function inspectApplication()
    {
        $this->commands = [];
        $this->namespaces = [];

        $all = $this->application->all($this->namespace ? $this->application->findNamespace($this->namespace) : null);
        foreach ($this->sortCommands($all) as $namespace => $commands) {
            $names = [];

            /** @var Command $command */
            foreach ($commands as $name => $command) {
                if (!$command->getName() || (!$this->showHidden && $command->isHidden())) {
                    continue;
                }

                if ($command->getName() === $name) {
                    $this->commands[$name] = $command;
                } else {
                    $this->aliases[$name] = $command;
                }

                $names[] = $name;
            }

            $this->namespaces[$namespace] = ['id' => $namespace, 'commands' => $names];
        }
    }

    private function sortCommands(array $commands): array
    {
        $namespacedCommands = [];
        $globalCommands = [];
        $sortedCommands = [];
        foreach ($commands as $name => $command) {
            $key = $this->application->extractNamespace($name, 1);
            if (\in_array($key, ['', self::GLOBAL_NAMESPACE], true)) {
                $globalCommands[$name] = $command;
            } else {
                $namespacedCommands[$key][$name] = $command;
            }
        }

        if ($globalCommands) {
            ksort($globalCommands);
            $sortedCommands[self::GLOBAL_NAMESPACE] = $globalCommands;
        }

        if ($namespacedCommands) {
            ksort($namespacedCommands, \SORT_STRING);
            foreach ($namespacedCommands as $key => $commandsSet) {
                ksort($commandsSet);
                $sortedCommands[$key] = $commandsSet;
            }
        }

        return $sortedCommands;
    }
}
