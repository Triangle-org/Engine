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

use Symfony\Component\Process\Process;
use Triangle\Engine\Console\Completion\CompletionInput;
use Triangle\Engine\Console\Completion\CompletionSuggestions;
use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Input\InputOption;
use Triangle\Engine\Console\Output\ConsoleOutputInterface;
use Triangle\Engine\Console\Output\OutputInterface;
use const PATHINFO_EXTENSION;

/**
 * Dumps the completion script for the current shell.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class DumpCompletionCommand extends Command
{
    protected static ?string $defaultName = 'completion';
    protected static ?string $defaultDescription = 'Dump the shell completion script';

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('shell')) {
            $suggestions->suggestValues($this->getSupportedShells());
        }
    }

    protected function configure(): void
    {
        $fullCommand = $_SERVER['PHP_SELF'];
        $commandName = basename($fullCommand);
        $fullCommand = @realpath($fullCommand) ?: $fullCommand;

        $this
            ->setHelp(<<<EOH
The <info>%command.name%</> command dumps the shell completion script required
to use shell autocompletion (currently only bash completion is supported).

<comment>Static installation
-------------------</>

Dump the script to a global completion file and restart your shell:

    <info>%command.full_name% bash | sudo tee /etc/bash_completion.d/$commandName</>

Or dump the script to a local file and source it:

    <info>%command.full_name% bash > completion.sh</>

    <comment># source the file whenever you use the project</>
    <info>source completion.sh</>

    <comment># or add this line at the end of your "~/.bashrc" file:</>
    <info>source /path/to/completion.sh</>

<comment>Dynamic installation
--------------------</>

Add this to the end of your shell configuration file (e.g. <info>"~/.bashrc"</>):

    <info>eval "$($fullCommand completion bash)"</>
EOH
            )
            ->addArgument('shell', InputArgument::OPTIONAL, 'The shell type (e.g. "bash"), the value of the "$SHELL" env var will be used if this is not given')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Tail the completion debug log');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandName = basename($_SERVER['argv'][0]);

        if ($input->getOption('debug')) {
            $this->tailDebugLog($commandName, $output);

            return self::SUCCESS;
        }

        $shell = $input->getArgument('shell') ?? self::guessShell();
        $completionFile = __DIR__ . '/../Resources/completion.' . $shell;
        if (!file_exists($completionFile)) {
            $supportedShells = $this->getSupportedShells();

            if ($output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }
            if ($shell) {
                $output->writeln(sprintf('<error>Detected shell "%s", which is not supported by Symfony shell completion (supported shells: "%s").</>', $shell, implode('", "', $supportedShells)));
            } else {
                $output->writeln(sprintf('<error>Shell not detected, Symfony shell completion only supports "%s").</>', implode('", "', $supportedShells)));
            }

            return self::INVALID;
        }

        $output->write(str_replace(['{{ COMMAND_NAME }}', '{{ VERSION }}'], [$commandName, $this->getApplication()->getVersion()], file_get_contents($completionFile)));

        return self::SUCCESS;
    }

    private static function guessShell(): string
    {
        return basename($_SERVER['SHELL'] ?? '');
    }

    private function tailDebugLog(string $commandName, OutputInterface $output): void
    {
        $debugFile = sys_get_temp_dir() . '/sf_' . $commandName . '.log';
        if (!file_exists($debugFile)) {
            touch($debugFile);
        }
        $process = new Process(['tail', '-f', $debugFile], null, null, null, 0);
        $process->run(function (string $type, string $line) use ($output): void {
            $output->write($line);
        });
    }

    /**
     * @return string[]
     */
    private function getSupportedShells(): array
    {
        return array_map(function ($f) {
            return pathinfo($f, PATHINFO_EXTENSION);
        }, glob(__DIR__ . '/../Resources/completion.*'));
    }
}
