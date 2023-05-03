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
use Triangle\Engine\Console\Formatter\OutputFormatter;
use Triangle\Engine\Console\Helper\Helper;
use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Input\InputDefinition;
use Triangle\Engine\Console\Input\InputOption;

/**
 * Text descriptor.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 *
 * @internal
 */
class TextDescriptor extends Descriptor
{
    /**
     * {@inheritdoc}
     */
    protected function describeInputArgument(InputArgument $argument, array $options = [])
    {
        if (null !== $argument->getDefault() && (!\is_array($argument->getDefault()) || \count($argument->getDefault()))) {
            $default = sprintf('<comment> [по умолчаниию: %s]</comment>', $this->formatDefaultValue($argument->getDefault()));
        } else {
            $default = '';
        }

        $totalWidth = $options['total_width'] ?? Helper::width($argument->getName());
        $spacingWidth = $totalWidth - \strlen($argument->getName());

        $this->writeText(sprintf(
            '  <info>%s</info>  %s%s%s',
            $argument->getName(),
            str_repeat(' ', $spacingWidth),
            // + 4 = 2 spaces before <info>, 2 spaces after </info>
            preg_replace('/\s*[\r\n]\s*/', "\n" . str_repeat(' ', $totalWidth + 4), $argument->getDescription()),
            $default
        ), $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function describeInputOption(InputOption $option, array $options = [])
    {
        if ($option->acceptValue() && null !== $option->getDefault() && (!\is_array($option->getDefault()) || \count($option->getDefault()))) {
            $default = sprintf('<comment> [по умолчаниию: %s]</comment>', $this->formatDefaultValue($option->getDefault()));
        } else {
            $default = '';
        }

        $value = '';
        if ($option->acceptValue()) {
            $value = '=' . strtoupper($option->getName());

            if ($option->isValueOptional()) {
                $value = '[' . $value . ']';
            }
        }

        $totalWidth = $options['total_width'] ?? $this->calculateTotalWidthForOptions([$option]);
        $synopsis = sprintf(
            '%s%s',
            $option->getShortcut() ? sprintf('-%s, ', $option->getShortcut()) : '    ',
            sprintf($option->isNegatable() ? '--%1$s|--no-%1$s' : '--%1$s%2$s', $option->getName(), $value)
        );

        $spacingWidth = $totalWidth - Helper::width($synopsis);

        $this->writeText(sprintf(
            '  <info>%s</info>  %s%s%s%s',
            $synopsis,
            str_repeat(' ', $spacingWidth),
            // + 4 = 2 spaces before <info>, 2 spaces after </info>
            preg_replace('/\s*[\r\n]\s*/', "\n" . str_repeat(' ', $totalWidth + 4), $option->getDescription()),
            $default,
            $option->isArray() ? '<comment> (поддерживается несколько значений)</comment>' : ''
        ), $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function describeInputDefinition(InputDefinition $definition, array $options = [])
    {
        $totalWidth = $this->calculateTotalWidthForOptions($definition->getOptions());
        foreach ($definition->getArguments() as $argument) {
            $totalWidth = max($totalWidth, Helper::width($argument->getName()));
        }

        if ($definition->getArguments()) {
            $this->writeText('<comment>Аргументы:</comment>', $options);
            $this->writeText("\n");
            foreach ($definition->getArguments() as $argument) {
                $this->describeInputArgument($argument, array_merge($options, ['total_width' => $totalWidth]));
                $this->writeText("\n");
            }
        }

        if ($definition->getArguments() && $definition->getOptions()) {
            $this->writeText("\n");
        }

        if ($definition->getOptions()) {
            $laterOptions = [];

            $this->writeText('<comment>Опции:</comment>', $options);
            foreach ($definition->getOptions() as $option) {
                if (\strlen($option->getShortcut() ?? '') > 1) {
                    $laterOptions[] = $option;
                    continue;
                }
                $this->writeText("\n");
                $this->describeInputOption($option, array_merge($options, ['total_width' => $totalWidth]));
            }
            foreach ($laterOptions as $option) {
                $this->writeText("\n");
                $this->describeInputOption($option, array_merge($options, ['total_width' => $totalWidth]));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function describeCommand(Command $command, array $options = [])
    {
        $command->mergeApplicationDefinition(false);

        if ($description = $command->getDescription()) {
            $this->writeText('<comment>Описание:</comment>', $options);
            $this->writeText("\n");
            $this->writeText('  ' . $description);
            $this->writeText("\n\n");
        }

        $this->writeText('<comment>Использование:</comment>', $options);
        foreach (array_merge([$command->getSynopsis(true)], $command->getAliases(), $command->getUsages()) as $usage) {
            $this->writeText("\n");
            $this->writeText('  ' . OutputFormatter::escape($usage), $options);
        }
        $this->writeText("\n");

        $definition = $command->getDefinition();
        if ($definition->getOptions() || $definition->getArguments()) {
            $this->writeText("\n");
            $this->describeInputDefinition($definition, $options);
            $this->writeText("\n");
        }

        $help = $command->getProcessedHelp();
        if ($help && $help !== $description) {
            $this->writeText("\n");
            $this->writeText('<comment>Справка:</comment>', $options);
            $this->writeText("\n");
            $this->writeText('  ' . str_replace("\n", "\n  ", $help), $options);
            $this->writeText("\n");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function describeApplication(Application $application, array $options = [])
    {
        $describedNamespace = $options['namespace'] ?? null;
        $description = new ApplicationDescription($application, $describedNamespace);

        if (isset($options['raw_text']) && $options['raw_text']) {
            $width = $this->getColumnWidth($description->getCommands());

            foreach ($description->getCommands() as $command) {
                $this->writeText(sprintf("%-{$width}s %s", $command->getName(), $command->getDescription()), $options);
                $this->writeText("\n");
            }
        } else {
            if ('' != $help = $application->getHelp()) {
                $this->writeText("$help\n\n", $options);
            }

            $this->writeText("<comment>Использование:</comment>\n", $options);
            $this->writeText("  команда [опции] [аргументы]\n\n", $options);

            $this->describeInputDefinition(new InputDefinition($application->getDefinition()->getOptions()), $options);

            $this->writeText("\n");
            $this->writeText("\n");

            $commands = $description->getCommands();
            $namespaces = $description->getNamespaces();
            if ($describedNamespace && $namespaces) {
                // make sure all alias commands are included when describing a specific namespace
                $describedNamespaceInfo = reset($namespaces);
                foreach ($describedNamespaceInfo['commands'] as $name) {
                    $commands[$name] = $description->getCommand($name);
                }
            }

            // calculate max. width based on available commands per namespace
            $width = $this->getColumnWidth(array_merge(...array_values(array_map(function ($namespace) use ($commands) {
                return array_intersect($namespace['commands'], array_keys($commands));
            }, array_values($namespaces)))));

            if ($describedNamespace) {
                $this->writeText(sprintf('<comment>Доступные команды для "%s":</comment>', $describedNamespace), $options);
            } else {
                $this->writeText('<comment>Доступные команды:</comment>', $options);
            }

            foreach ($namespaces as $namespace) {
                $namespace['commands'] = array_filter($namespace['commands'], function ($name) use ($commands) {
                    return isset($commands[$name]);
                });

                if (!$namespace['commands']) {
                    continue;
                }

                if (!$describedNamespace && ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id']) {
                    $this->writeText("\n");
                    $this->writeText(' <comment>' . $namespace['id'] . '</comment>', $options);
                }

                foreach ($namespace['commands'] as $name) {
                    $this->writeText("\n");
                    $spacingWidth = $width - Helper::width($name);
                    $command = $commands[$name];
                    $commandAliases = $name === $command->getName() ? $this->getCommandAliasesText($command) : '';
                    $this->writeText(sprintf('  <info>%s</info>%s%s', $name, str_repeat(' ', $spacingWidth), $commandAliases . $command->getDescription()), $options);
                }
            }

            $this->writeText("\n");
        }
    }

    /**
     * {@inheritdoc}
     */
    private function writeText(string $content, array $options = [])
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            isset($options['raw_output']) ? !$options['raw_output'] : true
        );
    }

    /**
     * Formats command aliases to show them in the command description.
     */
    private function getCommandAliasesText(Command $command): string
    {
        $text = '';
        $aliases = $command->getAliases();

        if ($aliases) {
            $text = '[' . implode('|', $aliases) . '] ';
        }

        return $text;
    }

    /**
     * Formats input option/argument default value.
     *
     * @param mixed $default
     */
    private function formatDefaultValue($default): string
    {
        if (\INF === $default) {
            return 'INF';
        }

        if (\is_string($default)) {
            $default = OutputFormatter::escape($default);
        } elseif (\is_array($default)) {
            foreach ($default as $key => $value) {
                if (\is_string($value)) {
                    $default[$key] = OutputFormatter::escape($value);
                }
            }
        }

        return str_replace('\\\\', '\\', json_encode($default, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param array<Command|string> $commands
     */
    private function getColumnWidth(array $commands): int
    {
        $widths = [];

        foreach ($commands as $command) {
            if ($command instanceof Command) {
                $widths[] = Helper::width($command->getName());
                foreach ($command->getAliases() as $alias) {
                    $widths[] = Helper::width($alias);
                }
            } else {
                $widths[] = Helper::width($command);
            }
        }

        return $widths ? max($widths) + 2 : 0;
    }

    /**
     * @param InputOption[] $options
     */
    private function calculateTotalWidthForOptions(array $options): int
    {
        $totalWidth = 0;
        foreach ($options as $option) {
            // "-" + shortcut + ", --" + name
            $nameLength = 1 + max(Helper::width($option->getShortcut()), 1) + 4 + Helper::width($option->getName());
            if ($option->isNegatable()) {
                $nameLength += 6 + Helper::width($option->getName()); // |--no- + name
            } elseif ($option->acceptValue()) {
                $valueLength = 1 + Helper::width($option->getName()); // = + value
                $valueLength += $option->isValueOptional() ? 2 : 0; // [ + ]

                $nameLength += $valueLength;
            }
            $totalWidth = max($totalWidth, $nameLength);
        }

        return $totalWidth;
    }
}
