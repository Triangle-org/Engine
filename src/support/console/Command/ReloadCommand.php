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

use support\App;
use support\console\Command\Command;
use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;
use support\console\Input\InputOption;

class ReloadCommand extends Command
{
    protected static $defaultName = 'reload';
    protected static $defaultDescription = 'Перезагрузить код. Используй -g для плавной перезагрузки.';

    protected function configure(): void
    {
        $this
            ->addOption('graceful', 'd', InputOption::VALUE_NONE, 'плавная перезагрузка');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        App::run();
        return self::SUCCESS;
    }
}
