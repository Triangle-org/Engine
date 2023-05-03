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

use Triangle\Engine\Console\Completion\CompletionInput;
use Triangle\Engine\Console\Completion\CompletionSuggestions;
use Triangle\Engine\Console\Descriptor\ApplicationDescription;
use Triangle\Engine\Console\Helper\DescriptorHelper;
use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Input\InputOption;
use Triangle\Engine\Console\Output\OutputInterface;

/**
 * ListCommand displays the list of all available commands for the application.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ListCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('list')
            ->setDefinition([
                new InputArgument('namespace', InputArgument::OPTIONAL, 'Имя пространства'),
                new InputOption('raw', null, InputOption::VALUE_NONE, 'Чтобы вывести необработанный список команд'),
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'Выходной формат (txt, xml, json или md)', 'txt'),
                new InputOption('short', null, InputOption::VALUE_NONE, 'Пропустить описание аргументов команд'),
            ])
            ->setDescription('Список команд')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all commands:

  <info>%command.full_name%</info>

You can also display the commands for a specific namespace:

  <info>%command.full_name% test</info>

You can also output the information in other formats by using the <comment>--format</comment> option:

  <info>%command.full_name% --format=xml</info>

It's also possible to get raw list of commands (useful for embedding command runner):

  <info>%command.full_name% --raw</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->getApplication(), [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
            'namespace' => $input->getArgument('namespace'),
            'short' => $input->getOption('short'),
        ]);

        return 0;
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('namespace')) {
            $descriptor = new ApplicationDescription($this->getApplication());
            $suggestions->suggestValues(array_keys($descriptor->getNamespaces()));

            return;
        }

        if ($input->mustSuggestOptionValuesFor('format')) {
            $helper = new DescriptorHelper();
            $suggestions->suggestValues($helper->getFormats());
        }
    }
}
