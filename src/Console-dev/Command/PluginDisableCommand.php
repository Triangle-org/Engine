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

use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;


class PluginDisableCommand extends Command
{
    protected static ?string $defaultName = 'plugin:disable';
    protected static ?string $defaultDescription = 'Отключить плагин';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название плагина (framex/plugin)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Отключение плагина $name");
        if (!strpos($name, '/')) {
            $output->writeln('<error>Некорректное название, оно должно содержать символ \'/\' , например framex/plugin</error>');
            return self::FAILURE;
        }
        $config_file = config_path() . "/plugin/$name/app.php";
        if (!is_file($config_file)) {
            return self::SUCCESS;
        }
        $config = include $config_file;
        if (empty($config['enable'])) {
            return self::SUCCESS;
        }
        $config_content = file_get_contents($config_file);
        $config_content = preg_replace('/(\'enable\' *?=> *?)(true)/', '$1false', $config_content);
        file_put_contents($config_file, $config_content);
        return self::SUCCESS;
    }
}
