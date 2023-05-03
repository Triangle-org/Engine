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

use Triangle\Engine\Console\Event\ConsoleCommandEvent;
use Triangle\Engine\Console\Event\ConsoleErrorEvent;
use Triangle\Engine\Console\Event\ConsoleSignalEvent;
use Triangle\Engine\Console\Event\ConsoleTerminateEvent;

/**
 * Contains all events dispatched by an Application.
 *
 * @author Francesco Levorato <git@flevour.net>
 */
final class ConsoleEvents
{
    /**
     * The COMMAND event allows you to attach listeners before any command is
     * executed by the console. It also allows you to modify the command, input and output
     * before they are handed to the command.
     *
     * @Event("Triangle\Engine\Console\Event\ConsoleCommandEvent")
     */
    public const COMMAND = 'console.command';

    /**
     * The SIGNAL event allows you to perform some actions
     * after the command execution was interrupted.
     *
     * @Event("Triangle\Engine\Console\Event\ConsoleSignalEvent")
     */
    public const SIGNAL = 'console.signal';

    /**
     * The TERMINATE event allows you to attach listeners after a command is
     * executed by the console.
     *
     * @Event("Triangle\Engine\Console\Event\ConsoleTerminateEvent")
     */
    public const TERMINATE = 'console.terminate';

    /**
     * The ERROR event occurs when an uncaught exception or error appears.
     *
     * This event allows you to deal with the exception/error or
     * to modify the thrown exception.
     *
     * @Event("Triangle\Engine\Console\Event\ConsoleErrorEvent")
     */
    public const ERROR = 'console.error';

    /**
     * Event aliases.
     *
     * These aliases can be consumed by RegisterListenersPass.
     */
    public const ALIASES = [
        ConsoleCommandEvent::class => self::COMMAND,
        ConsoleErrorEvent::class => self::ERROR,
        ConsoleSignalEvent::class => self::SIGNAL,
        ConsoleTerminateEvent::class => self::TERMINATE,
    ];
}
