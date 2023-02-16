<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Formatter;

/**
 * Formatter interface for console output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutputFormatterInterface
{
    /**
     * Sets the decorated flag.
     */
    public function setDecorated(bool $decorated);

    /**
     * Whether the output will decorate messages.
     *
     * @return bool
     */
    public function isDecorated();

    /**
     * Sets a new style.
     */
    public function setStyle(string $name, OutputFormatterStyleInterface $style);

    /**
     * Checks if output formatter has style with specified name.
     *
     * @return bool
     */
    public function hasStyle(string $name);

    /**
     * Gets style options from style with specified name.
     *
     * @return OutputFormatterStyleInterface
     *
     * @throws \InvalidArgumentException When style isn't defined
     */
    public function getStyle(string $name);

    /**
     * Formats a message according to the given styles.
     *
     * @return string|null
     */
    public function format(?string $message);
}
