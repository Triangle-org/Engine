<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\SignalRegistry;

final class SignalRegistry
{
    private $signalHandlers = [];

    public function __construct()
    {
        if (\function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        }
    }

    public function register(int $signal, callable $signalHandler): void
    {
        if (!isset($this->signalHandlers[$signal])) {
            $previousCallback = pcntl_signal_get_handler($signal);

            if (\is_callable($previousCallback)) {
                $this->signalHandlers[$signal][] = $previousCallback;
            }
        }

        $this->signalHandlers[$signal][] = $signalHandler;

        pcntl_signal($signal, [$this, 'handle']);
    }

    public static function isSupported(): bool
    {
        if (!\function_exists('pcntl_signal')) {
            return false;
        }

        if (\in_array('pcntl_signal', explode(',', \ini_get('disable_functions')))) {
            return false;
        }

        return true;
    }

    /**
     * @internal
     */
    public function handle(int $signal): void
    {
        $count = \count($this->signalHandlers[$signal]);

        foreach ($this->signalHandlers[$signal] as $i => $signalHandler) {
            $hasNext = $i !== $count - 1;
            $signalHandler($signal, $hasNext);
        }
    }
}
