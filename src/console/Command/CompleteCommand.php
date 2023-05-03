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

use RuntimeException;
use Throwable;
use Triangle\Engine\Console\Completion\CompletionInput;
use Triangle\Engine\Console\Completion\CompletionSuggestions;
use Triangle\Engine\Console\Completion\Output\BashCompletionOutput;
use Triangle\Engine\Console\Completion\Output\CompletionOutputInterface;
use Triangle\Engine\Console\Exception\CommandNotFoundException;
use Triangle\Engine\Console\Exception\ExceptionInterface;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Input\InputOption;
use Triangle\Engine\Console\Output\OutputInterface;
use function get_class;
use function in_array;
use const FILE_APPEND;
use const FILTER_VALIDATE_BOOLEAN;
use const PHP_EOL;

/**
 * Responsible for providing the values to the shell completion.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class CompleteCommand extends Command
{
    protected static ?string $defaultName = '|_complete';
    protected static ?string $defaultDescription = 'Internal command to provide shell completion suggestions';

    private array $completionOutputs;

    private bool $isDebug = false;

    /**
     * @param array<string, class-string<CompletionOutputInterface>> $completionOutputs A list of additional completion outputs, with shell name as key and FQCN as value
     * @throws \ReflectionException
     */
    public function __construct(array $completionOutputs = [])
    {
        // must be set before the parent constructor, as the property value is used in configure()
        $this->completionOutputs = $completionOutputs + ['bash' => BashCompletionOutput::class];

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('shell', 's', InputOption::VALUE_REQUIRED, 'The shell type ("' . implode('", "', array_keys($this->completionOutputs)) . '")')
            ->addOption('input', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'An array of input tokens (e.g. COMP_WORDS or argv)')
            ->addOption('current', 'c', InputOption::VALUE_REQUIRED, 'The index of the "input" array that the cursor is in (e.g. COMP_CWORD)')
            ->addOption('symfony', 'S', InputOption::VALUE_REQUIRED, 'The version of the completion script');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->isDebug = filter_var(getenv('SYMFONY_COMPLETION_DEBUG'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // uncomment when a bugfix or BC break has been introduced in the shell completion scripts
            // $version = $input->getOption('symfony');
            // if ($version && version_compare($version, 'x.y', '>=')) {
            //    $message = sprintf('Completion script version is not supported ("%s" given, ">=x.y" required).', $version);
            //    $this->log($message);

            //    $output->writeln($message.' Install the Symfony completion script again by using the "completion" command.');

            //    return 126;
            // }

            $shell = $input->getOption('shell');
            if (!$shell) {
                throw new RuntimeException('The "--shell" option must be set.');
            }

            if (!$completionOutput = $this->completionOutputs[$shell] ?? false) {
                throw new RuntimeException(sprintf('Shell completion is not supported for your shell: "%s" (supported: "%s").', $shell, implode('", "', array_keys($this->completionOutputs))));
            }

            $completionInput = $this->createCompletionInput($input);
            $suggestions = new CompletionSuggestions();

            $this->log([
                '',
                '<comment>' . date('Y-m-d H:i:s') . '</>',
                '<info>Input:</> <comment>("|" indicates the cursor position)</>',
                '  ' . $completionInput,
                '<info>Command:</>',
                '  ' . implode(' ', $_SERVER['argv']),
                '<info>Messages:</>',
            ]);

            $command = $this->findCommand($completionInput);
            if (null === $command) {
                $this->log('  No command found, completing using the Application class.');

                $this->getApplication()->complete($completionInput, $suggestions);
            } elseif (
                $completionInput->mustSuggestArgumentValuesFor('command')
                && $command->getName() !== $completionInput->getCompletionValue()
                && !in_array($completionInput->getCompletionValue(), $command->getAliases(), true)
            ) {
                $this->log('  No command found, completing using the Application class.');

                // expand shortcut names ("cache:cl<TAB>") into their full name ("cache:clear")
                $suggestions->suggestValues(array_filter(array_merge([$command->getName()], $command->getAliases())));
            } else {
                $command->mergeApplicationDefinition();
                $completionInput->bind($command->getDefinition());

                if (CompletionInput::TYPE_OPTION_NAME === $completionInput->getCompletionType()) {
                    $this->log('  Completing option names for the <comment>' . get_class($command instanceof LazyCommand ? $command->getCommand() : $command) . '</> command.');

                    $suggestions->suggestOptions($command->getDefinition()->getOptions());
                } else {
                    $this->log([
                        '  Completing using the <comment>' . get_class($command instanceof LazyCommand ? $command->getCommand() : $command) . '</> class.',
                        '  Completing <comment>' . $completionInput->getCompletionType() . '</> for <comment>' . $completionInput->getCompletionName() . '</>',
                    ]);
                    if (null !== $compval = $completionInput->getCompletionValue()) {
                        $this->log('  Current value: <comment>' . $compval . '</>');
                    }

                    $command->complete($completionInput, $suggestions);
                }
            }

            /** @var CompletionOutputInterface $completionOutput */
            $completionOutput = new $completionOutput();

            $this->log('<info>Suggestions:</>');
            if ($options = $suggestions->getOptionSuggestions()) {
                $this->log('  --' . implode(' --', array_map(function ($o) {
                        return $o->getName();
                    }, $options)));
            } elseif ($values = $suggestions->getValueSuggestions()) {
                $this->log('  ' . implode(' ', $values));
            } else {
                $this->log('  <comment>No suggestions were provided</>');
            }

            $completionOutput->write($suggestions, $output);
        } catch (Throwable $e) {
            $this->log([
                '<error>Error!</error>',
                (string)$e,
            ]);

            if ($output->isDebug()) {
                throw $e;
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function createCompletionInput(InputInterface $input): CompletionInput
    {
        $currentIndex = $input->getOption('current');
        if (!$currentIndex || !ctype_digit($currentIndex)) {
            throw new RuntimeException('The "--current" option must be set and it must be an integer.');
        }

        $completionInput = CompletionInput::fromTokens($input->getOption('input'), (int)$currentIndex);

        try {
            $completionInput->bind($this->getApplication()->getDefinition());
        } catch (ExceptionInterface) {
        }

        return $completionInput;
    }

    private function findCommand(CompletionInput $completionInput): ?Command
    {
        try {
            $inputName = $completionInput->getFirstArgument();
            if (null === $inputName) {
                return null;
            }

            return $this->getApplication()->find($inputName);
        } catch (CommandNotFoundException) {
        }

        return null;
    }

    private function log($messages): void
    {
        if (!$this->isDebug) {
            return;
        }

        $commandName = basename($_SERVER['argv'][0]);
        file_put_contents(sys_get_temp_dir() . '/sf_' . $commandName . '.log', implode(PHP_EOL, (array)$messages) . PHP_EOL, FILE_APPEND);
    }
}
