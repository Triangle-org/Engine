<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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

namespace support;

/**
 * Простой сборщик данных
 */
final class Collection
{
    /**
     * Данные
     *
     * @var mixed
     */
    protected $collection = null;

    /**
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->collection = new \stdClass();

        if (is_object($data)) {
            $this->collection = $data;
        }

        $this->collection = (object)$data;
    }

    /**
     * Извлекает всю коллекцию в виде массива
     *
     * @return mixed
     */
    public function toArray()
    {
        return (array)$this->collection;
    }

    /**
     * Извлекает элемент
     *
     * @param $property
     *
     * @return mixed
     */
    public function get($property)
    {
        if ($this->exists($property)) {
            return $this->collection->$property;
        }

        return null;
    }

    /**
     * Добавить или обновить элемент
     *
     * @param $property
     * @param mixed $value
     */
    public function set($property, $value)
    {
        if ($property) {
            $this->collection->$property = $value;
        }
    }

    /**
     * @param $property
     *
     * @return Collection
     */
    public function filter($property)
    {
        if ($this->exists($property)) {
            $data = $this->get($property);

            if (!is_a($data, 'Collection')) {
                $data = new Collection($data);
            }

            return $data;
        }

        return new Collection([]);
    }

    /**
     * Проверяет, есть ли элемент в коллекции
     *
     * @param $property
     *
     * @return bool
     */
    public function exists($property)
    {
        return property_exists($this->collection, $property);
    }

    /**
     * Определяет, пуста ли коллекция
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !(bool)$this->count();
    }

    /**
     * Подсчитать все предметы в коллекции
     *
     * @return int
     */
    public function count()
    {
        return count($this->properties());
    }

    /**
     * Возвращает имена всех свойств элементов
     *
     * @return array
     */
    public function properties()
    {
        $properties = [];

        foreach ($this->collection as $key => $value) {
            $properties[] = $key;
        }

        return $properties;
    }

    /**
     * Возвращает все значения элементов
     *
     * @return array
     */
    public function values()
    {
        $values = [];

        foreach ($this->collection as $value) {
            $values[] = $value;
        }

        return $values;
    }
}
