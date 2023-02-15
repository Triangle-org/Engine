<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
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
    protected $instances = [];
    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * Получить
     * @param string $name
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $name)
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
    public function make(string $name, array $constructor = [])
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
