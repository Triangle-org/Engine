<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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

namespace support\console\Command;

use support\console\Completion\CompletionInput;
use support\console\Completion\CompletionSuggestions;
use support\console\Descriptor\ApplicationDescription;
use support\console\Helper\DescriptorHelper;
use support\console\Input\InputArgument;
use support\console\Input\InputInterface;
use support\console\Input\InputOption;
use support\console\Output\OutputInterface;

/**
 * HelpCommand displays the help for a given command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HelpCommand extends Command
{
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('help')
            ->setDefinition([
                new InputArgument('command_name', InputArgument::OPTIONAL, 'Название команды', 'help'),
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'Формат вывода (txt, xml, json, or md)', 'txt'),
                new InputOption('raw', null, InputOption::VALUE_NONE, 'Черновой вывод справки'),
            ])
            ->setDescription('Отображает справку о командах')
            ->setHelp(<<<'EOF'
Команда <info>%command.name%</info> отображает справку о любой команде:

    "<info>%command.full_name% list</info>" - справка о команде "list"

Вы можете получить вывод в разных форматах, используя опцию <comment>--format</comment>:

    <info>%command.full_name% --format=xml list</info>

Для отображения списка доступных команд используйте команду "<info>list</info>".
EOF
            )
        ;
    }

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->find($input->getArgument('command_name'));
        }

        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
        ]);

        $this->command = null;

        return 0;
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('command_name')) {
            $descriptor = new ApplicationDescription($this->getApplication());
            $suggestions->suggestValues(array_keys($descriptor->getCommands()));

            return;
        }

        if ($input->mustSuggestOptionValuesFor('format')) {
            $helper = new DescriptorHelper();
            $suggestions->suggestValues($helper->getFormats());
        }
    }
}
