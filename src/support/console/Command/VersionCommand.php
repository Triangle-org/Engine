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

use support\console\Command\Command;
use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected static $defaultName = 'version';
    protected static $defaultDescription = 'Показать версии Triangle';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $installed_file = base_path() . '/vendor/composer/installed.php';
        if (is_file($installed_file)) {
            $version_info = include $installed_file;
            $output->writeln(print_r($version_info['versions'], true));
        } else {
            $output->writeln("Файла $installed_file не существует");
        }

        foreach (['localzet/server', 'triangle/engine', 'triangle/web'] as $package) {
            $out = '';
            if (isset($version_info['versions'][$package])) {
                $output->writeln('Пакет Triangle v2');
                switch ($package) {
                    case 'localzet/server':
                        $out = 'Localzet Server';
                        break;
                    case 'triangle/engine':
                        $out .= 'Triangle Engine';
                        break;
                    case 'triangle/web':
                        $out = 'Triangle Web';
                        break;
                }
                $output->writeln($out . ': ' . $version_info['versions'][$package]['pretty_version']);
            }
        }

        return self::SUCCESS;
    }
}
