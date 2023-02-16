<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console;

use support\console\Event\ConsoleCommandEvent;
use support\console\Event\ConsoleErrorEvent;
use support\console\Event\ConsoleSignalEvent;
use support\console\Event\ConsoleTerminateEvent;

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
     * @Event("support\console\Event\ConsoleCommandEvent")
     */
    public const COMMAND = 'console.command';

    /**
     * The SIGNAL event allows you to perform some actions
     * after the command execution was interrupted.
     *
     * @Event("support\console\Event\ConsoleSignalEvent")
     */
    public const SIGNAL = 'console.signal';

    /**
     * The TERMINATE event allows you to attach listeners after a command is
     * executed by the console.
     *
     * @Event("support\console\Event\ConsoleTerminateEvent")
     */
    public const TERMINATE = 'console.terminate';

    /**
     * The ERROR event occurs when an uncaught exception or error appears.
     *
     * This event allows you to deal with the exception/error or
     * to modify the thrown exception.
     *
     * @Event("support\console\Event\ConsoleErrorEvent")
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
