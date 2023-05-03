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

use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected static ?string $defaultName = 'version';
    protected static ?string $defaultDescription = 'Показать версии Triangle';

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
        } else {
            $output->writeln("Файла $installed_file не существует");
        }

        foreach (['localzet/core', 'localzet/framex', 'localzet/webkit', 'localzet/server', 'triangle/engine', 'triangle/web'] as $package) {
            $out = '';
            if (isset($version_info['versions'][$package])) {
                switch ($package) {
                    // Server
                    case 'localzet/core':
                    case 'localzet/server':
                        $out = 'Сервер:     Localzet Server';
                        break;

                    // Engine
                    case 'localzet/framex':
                        $out = 'Движок:     FrameX (FX) Engine';
                        break;
                    case 'triangle/engine':
                        $out = 'Движок:     Triangle Engine';
                        break;

                    // Framework
                    case 'localzet/webkit':
                        $out = 'Фреймворк:  Localzet WebKit';
                        break;
                    case 'triangle/web':
                        $out = 'Фреймворк:  Triangle Web';
                        break;
                }
                $output->writeln($out . ' (' . $version_info['versions'][$package]['pretty_version'] . ')');
            }
        }

        return self::SUCCESS;
    }
}
