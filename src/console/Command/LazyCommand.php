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

namespace Triangle\Engine\Console\Command;

use Closure;
use Triangle\Engine\Console\Application;
use Triangle\Engine\Console\Completion\CompletionInput;
use Triangle\Engine\Console\Completion\CompletionSuggestions;
use Triangle\Engine\Console\Helper\HelperSet;
use Triangle\Engine\Console\Input\InputDefinition;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class LazyCommand extends Command
{
    private $command;
    private ?bool $isEnabled;

    public function __construct(string $name, array $aliases, string $description, bool $isHidden, Closure $commandFactory, ?bool $isEnabled = true)
    {
        $this->setName($name)
            ->setAliases($aliases)
            ->setHidden($isHidden)
            ->setDescription($description);

        $this->command = $commandFactory;
        $this->isEnabled = $isEnabled;
    }

    public function ignoreValidationErrors(): void
    {
        $this->getCommand()->ignoreValidationErrors();
    }

    public function setApplication(Application $application = null): void
    {
        if ($this->command instanceof parent) {
            $this->command->setApplication($application);
        }

        parent::setApplication($application);
    }

    public function setHelperSet(HelperSet $helperSet): void
    {
        if ($this->command instanceof parent) {
            $this->command->setHelperSet($helperSet);
        }

        parent::setHelperSet($helperSet);
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled ?? $this->getCommand()->isEnabled();
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return $this->getCommand()->run($input, $output);
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        $this->getCommand()->complete($input, $suggestions);
    }

    /**
     * @param callable $code
     * @return $this
     * @throws \ReflectionException
     */
    public function setCode(callable $code): self
    {
        $this->getCommand()->setCode($code);

        return $this;
    }

    /**
     * @internal
     */
    public function mergeApplicationDefinition(bool $mergeArgs = true): void
    {
        $this->getCommand()->mergeApplicationDefinition($mergeArgs);
    }

    /**
     * @return $this
     */
    public function setDefinition($definition): self
    {
        $this->getCommand()->setDefinition($definition);

        return $this;
    }

    public function getDefinition(): InputDefinition
    {
        return $this->getCommand()->getDefinition();
    }

    public function getNativeDefinition(): InputDefinition
    {
        return $this->getCommand()->getNativeDefinition();
    }

    /**
     * @return $this
     */
    public function addArgument(string $name, int $mode = null, string $description = '', $default = null): self
    {
        $this->getCommand()->addArgument($name, $mode, $description, $default);

        return $this;
    }

    /**
     * @return $this
     */
    public function addOption(string $name, $shortcut = null, int $mode = null, string $description = '', $default = null): self
    {
        $this->getCommand()->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * @return $this
     */
    public function setProcessTitle(string $title): self
    {
        $this->getCommand()->setProcessTitle($title);

        return $this;
    }

    /**
     * @return $this
     */
    public function setHelp(string $help): self
    {
        $this->getCommand()->setHelp($help);

        return $this;
    }

    public function getHelp(): string
    {
        return $this->getCommand()->getHelp();
    }

    public function getProcessedHelp(): string
    {
        return $this->getCommand()->getProcessedHelp();
    }

    public function getSynopsis(bool $short = false): string
    {
        return $this->getCommand()->getSynopsis($short);
    }

    /**
     * @return $this
     */
    public function addUsage(string $usage): self
    {
        $this->getCommand()->addUsage($usage);

        return $this;
    }

    public function getUsages(): array
    {
        return $this->getCommand()->getUsages();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getHelper(string $name): mixed
    {
        return $this->getCommand()->getHelper($name);
    }

    public function getCommand(): parent
    {
        if (!$this->command instanceof Closure) {
            return $this->command;
        }

        $command = $this->command = ($this->command)();
        $command->setApplication($this->getApplication());

        if (null !== $this->getHelperSet()) {
            $command->setHelperSet($this->getHelperSet());
        }

        $command->setName($this->getName())
            ->setAliases($this->getAliases())
            ->setHidden($this->isHidden())
            ->setDescription($this->getDescription());

        // Will throw if the command is not correctly initialized.
        $command->getDefinition();

        return $command;
    }
}
