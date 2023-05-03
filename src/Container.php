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

namespace Triangle\Engine;

use Psr\Container\ContainerInterface;
use Triangle\Engine\Exception\NotFoundException;
use function array_key_exists;
use function class_exists;

/**
 * Class Container
 */
class Container implements ContainerInterface
{

    /**
     * @var array
     */
    protected array $instances = [];
    /**
     * @var array
     */
    protected array $definitions = [];

    /**
     * Получить
     * @param string $name
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $name): mixed
    {
        if (!isset($this->instances[$name])) {
            if (isset($this->definitions[$name])) {
                $this->instances[$name] = call_user_func($this->definitions[$name], $this);
            } else {
                if (!class_exists($name)) {
                    throw new NotFoundException("Класс '$name' не найден");
                }
                $this->instances[$name] = new $name();
            }
        }
        return $this->instances[$name];
    }

    /**
     * Существует?
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->instances)
            || array_key_exists($name, $this->definitions);
    }

    /**
     * Собрать
     * @param string $name
     * @param array $constructor
     * @return mixed
     * @throws NotFoundException
     */
    public function make(string $name, array $constructor = []): mixed
    {
        if (!class_exists($name)) {
            throw new NotFoundException("Класс '$name' не найден");
        }
        return new $name(...array_values($constructor));
    }

    /**
     * Добавить определения
     * @param array $definitions
     * @return $this
     */
    public function addDefinitions(array $definitions): Container
    {
        $this->definitions = array_merge($this->definitions, $definitions);
        return $this;
    }
}
