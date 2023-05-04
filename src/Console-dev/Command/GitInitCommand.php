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

class GitInitCommand extends Command
{
    protected static ?string $defaultName = 'git:connect|git-connect';
    protected static ?string $defaultDescription = 'Добавить удалённый репозиторий Git';

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
        if (empty(config('app.git'))) {
            $output->writeln("<error>Не задан app.git</>");
            return self::FAILURE;
        }


        exec('git config --global --add safe.directory ' . base_path());
        exec('cd ' . base_path() . ' && sudo git init .');
        $output->writeln("<info>Git инициирован</>");

        exec('cd ' . base_path() . ' && sudo git remote add origin ' . config('app.git'));
        $output->writeln("<info>Добавлен удалённый репозиторий</>");

        exec('cd ' . base_path() . ' && sudo git fetch origin');
        $output->writeln("<info>Получены данные</>");

        $output->writeln("<info>Репозиторий связан с удалённым</>");
        return self::SUCCESS;
    }
}
