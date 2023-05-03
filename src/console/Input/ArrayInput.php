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
use Triangle\Engine\Console\Exception\InvalidOptionException;

/**
 * ArrayInput represents an input provided as an array.
 *
 * Usage:
 *
 *     $input = new ArrayInput(['command' => 'foo:bar', 'foo' => 'bar', '--bar' => 'foobar']);
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ArrayInput extends Input
{
    private $parameters;

    public function __construct(array $parameters, InputDefinition $definition = null)
    {
        $this->parameters = $parameters;

        parent::__construct($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstArgument()
    {
        foreach ($this->parameters as $param => $value) {
            if ($param && \is_string($param) && '-' === $param[0]) {
                continue;
            }

            return $value;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values, bool $onlyParams = false)
    {
        $values = (array)$values;

        foreach ($this->parameters as $k => $v) {
            if (!\is_int($k)) {
                $v = $k;
            }

            if ($onlyParams && '--' === $v) {
                return false;
            }

            if (\in_array($v, $values)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        $values = (array)$values;

        foreach ($this->parameters as $k => $v) {
            if ($onlyParams && ('--' === $k || (\is_int($k) && '--' === $v))) {
                return $default;
            }

            if (\is_int($k)) {
                if (\in_array($v, $values)) {
                    return true;
                }
            } elseif (\in_array($k, $values)) {
                return $v;
            }
        }

        return $default;
    }

    /**
     * Returns a stringified representation of the args passed to the command.
     *
     * @return string
     */
    public function __toString()
    {
        $params = [];
        foreach ($this->parameters as $param => $val) {
            if ($param && \is_string($param) && '-' === $param[0]) {
                $glue = ('-' === $param[1]) ? '=' : ' ';
                if (\is_array($val)) {
                    foreach ($val as $v) {
                        $params[] = $param . ('' != $v ? $glue . $this->escapeToken($v) : '');
                    }
                } else {
                    $params[] = $param . ('' != $val ? $glue . $this->escapeToken($val) : '');
                }
            } else {
                $params[] = \is_array($val) ? implode(' ', array_map([$this, 'escapeToken'], $val)) : $this->escapeToken($val);
            }
        }

        return implode(' ', $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function parse()
    {
        foreach ($this->parameters as $key => $value) {
            if ('--' === $key) {
                return;
            }
            if (str_starts_with($key, '--')) {
                $this->addLongOption(substr($key, 2), $value);
            } elseif (str_starts_with($key, '-')) {
                $this->addShortOption(substr($key, 1), $value);
            } else {
                $this->addArgument($key, $value);
            }
        }
    }

    /**
     * Adds a short option value.
     *
     * @throws InvalidOptionException When option given doesn't exist
     */
    private function addShortOption(string $shortcut, $value)
    {
        if (!$this->definition->hasShortcut($shortcut)) {
            throw new InvalidOptionException(sprintf('The "-%s" option does not exist.', $shortcut));
        }

        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }

    /**
     * Adds a long option value.
     *
     * @throws InvalidOptionException When option given doesn't exist
     * @throws InvalidOptionException When a required value is missing
     */
    private function addLongOption(string $name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            if (!$this->definition->hasNegation($name)) {
                throw new InvalidOptionException(sprintf('The "--%s" option does not exist.', $name));
            }

            $optionName = $this->definition->negationToName($name);
            $this->options[$optionName] = false;

            return;
        }

        $option = $this->definition->getOption($name);

        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new InvalidOptionException(sprintf('The "--%s" option requires a value.', $name));
            }

            if (!$option->isValueOptional()) {
                $value = true;
            }
        }

        $this->options[$name] = $value;
    }

    /**
     * Adds an argument value.
     *
     * @param string|int $name The argument name
     * @param mixed $value The value for the argument
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    private function addArgument($name, $value)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }

        $this->arguments[$name] = $value;
    }
}
