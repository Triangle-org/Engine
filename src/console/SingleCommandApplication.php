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

namespace Triangle\Engine\Console;

use Triangle\Engine\Console\Command\Command;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class SingleCommandApplication extends Command
{
    private $version = 'UNKNOWN';
    private $autoExit = true;
    private $running = false;

    /**
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @final
     *
     * @return $this
     */
    public function setAutoExit(bool $autoExit): self
    {
        $this->autoExit = $autoExit;

        return $this;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        if ($this->running) {
            return parent::run($input, $output);
        }

        // We use the command name as the application name
        $application = new Application($this->getName() ?: 'UNKNOWN', $this->version);
        $application->setAutoExit($this->autoExit);
        // Fix the usage of the command displayed with "--help"
        $this->setName($_SERVER['argv'][0]);
        $application->add($this);
        $application->setDefaultCommand($this->getName(), true);

        $this->running = true;
        try {
            $ret = $application->run($input, $output);
        } finally {
            $this->running = false;
        }

        return $ret ?? 1;
    }
}
