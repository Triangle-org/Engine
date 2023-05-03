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

namespace Triangle\Engine\Console\Input;

use Triangle\Engine\Console\Exception\InvalidArgumentException;
use Triangle\Engine\Console\Exception\LogicException;

/**
 * Represents a command line argument.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InputArgument
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;

    private $name;
    private $mode;
    private $default;
    private $description;

    /**
     * @param string $name The argument name
     * @param int|null $mode The argument mode: self::REQUIRED or self::OPTIONAL
     * @param string $description A description text
     * @param string|bool|int|float|array|null $default The default value (for self::OPTIONAL mode only)
     *
     * @throws InvalidArgumentException When argument mode is not valid
     */
    public function __construct(string $name, int $mode = null, string $description = '', $default = null)
    {
        if (null === $mode) {
            $mode = self::OPTIONAL;
        } elseif ($mode > 7 || $mode < 1) {
            throw new InvalidArgumentException(sprintf('Argument mode "%s" is not valid.', $mode));
        }

        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;

        $this->setDefault($default);
    }

    /**
     * Returns the argument name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns true if the argument is required.
     *
     * @return bool true if parameter mode is self::REQUIRED, false otherwise
     */
    public function isRequired()
    {
        return self::REQUIRED === (self::REQUIRED & $this->mode);
    }

    /**
     * Returns true if the argument can take multiple values.
     *
     * @return bool true if mode is self::IS_ARRAY, false otherwise
     */
    public function isArray()
    {
        return self::IS_ARRAY === (self::IS_ARRAY & $this->mode);
    }

    /**
     * Sets the default value.
     *
     * @param string|bool|int|float|array|null $default
     *
     * @throws LogicException When incorrect default value is given
     */
    public function setDefault($default = null)
    {
        if ($this->isRequired() && null !== $default) {
            throw new LogicException('Cannot set a default value except for InputArgument::OPTIONAL mode.');
        }

        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array argument must be an array.');
            }
        }

        $this->default = $default;
    }

    /**
     * Returns the default value.
     *
     * @return string|bool|int|float|array|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Returns the description text.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
