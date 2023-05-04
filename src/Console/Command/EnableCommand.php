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

class EnableCommand extends Command
{
    protected static ?string $defaultName = 'supervisor:enable|enable';
    protected static ?string $defaultDescription = 'Добавить проект в автозагрузку';

    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!is_dir("/etc/supervisor/conf.d/")) {
            $output->writeln("<error>Для автозагрузки требуется Supervisor</>");
            return self::FAILURE;
        }

        if (empty(config('app.domain'))) {
            $output->writeln("<error>Не задан app.domain</>");
            return self::FAILURE;
        }

        $domain = config('app.domain');
        $directory = base_path();
        $file = $directory . "/resources/supervisor.conf";

        if (!is_file($file)) {
            $conf = <<<EOF
            [program:$domain]
            user = root
            command = php master restart
            directory = $directory
            numprocs = 1
            autorestart = true
            autostart = true
            EOF;

            $fstream = fopen($file, 'w');
            fwrite($fstream, $conf);
            fclose($fstream);

            $output->writeln("<comment>Конфигурация создана</>");
        }

        exec("ln -sf $file /etc/supervisor/conf.d/$domain.conf");
        $output->writeln("<info>Ссылка создана</>");

        exec("service supervisor restart");
        $output->writeln("<info>Supervisor перезапущен</>");

        return self::SUCCESS;
    }
}
