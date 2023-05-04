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

namespace Triangle\Engine\Console\Event;

/**
 * Allows to do things before the command is executed, like skipping the command or changing the input.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class ConsoleCommandEvent extends ConsoleEvent
{
    /**
     * The return code for skipped commands, this will also be passed into the terminate event.
     */
    public const RETURN_CODE_DISABLED = 113;

    /**
     * Indicates if the command should be run or skipped.
     */
    private $commandShouldRun = true;

    /**
     * Disables the command, so it won't be run.
     */
    public function disableCommand(): bool
    {
        return $this->commandShouldRun = false;
    }

    public function enableCommand(): bool
    {
        return $this->commandShouldRun = true;
    }

    /**
     * Returns true if the command is runnable, false otherwise.
     */
    public function commandShouldRun(): bool
    {
        return $this->commandShouldRun;
    }
}
