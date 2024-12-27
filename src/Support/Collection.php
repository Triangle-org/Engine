<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2024 Triangle Framework Team
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <support@localzet.com>
 */

namespace support;

/**
 * Класс Collection представляет собой гибкую коллекцию данных.
 * Это означает, что он может хранить различные типы данных, такие как строки, числа, массивы и объекты.
 */
class Collection
{
    /**
     * Коллекция данных.
     * Это свойство хранит все данные, которые были добавлены в коллекцию.
     */
    protected array $collection = [];

    /**
     * Конструктор класса Collection.
     * Этот метод принимает необязательный аргумент $data, который будет приведен к массиву и сохранен в свойстве $collection.
     *
     * @param mixed $data Начальные данные для коллекции, которые будут приведены к массиву.
     */
    public function __construct(mixed $data = null)
    {
        $this->collection = $this->normalizeData($data);
    }

    /**
     * Метод normalizeData нормализует входные данные в массив.
     *
     * @param mixed $data Входные данные.
     *
     * @return array Нормализованные данные в виде массива.
     */
    protected function normalizeData(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $data->toArray();
            }

            return (array)$data;
        }

        return [$data];
    }

    /**
     * Метод toArray возвращает всю коллекцию данных в виде массива.
     *
     * @return array Вся коллекция данных в виде массива.
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * Метод get возвращает значение указанного свойства из коллекции.
     * Если свойство не существует, метод возвращает null.
     *
     * @param string|int $property Имя свойства для получения.
     *
     * @return mixed Значение свойства или null, если его не существует.
     */
    public function get(string|int $property): mixed
    {
        return $this->collection[$property] ?? null;
    }

    /**
     * Метод set добавляет новое свойство в коллекцию или обновляет существующее.
     *
     * @param string|int $property Имя свойства для установки.
     * @param mixed $value Значение для установки.
     */
    public function set(string|int $property, mixed $value): void
    {
        $this->collection[$property] = $value;
    }

    /**
     * Метод filter возвращает новую коллекцию, которая содержит только элементы, соответствующие указанному свойству.
     * Если свойство не существует, метод возвращает пустую коллекцию.
     *
     * @param string|int $property Свойство для фильтрации.
     *
     * @return Collection Новая коллекция, содержащая отфильтрованные данные.
     */
    public function filter(string|int $property): Collection
    {
        $filtered = array_filter($this->collection, fn($key): bool => $key == $property, ARRAY_FILTER_USE_KEY);

        return new Collection($filtered);
    }

    /**
     * Метод exists проверяет, существует ли указанное свойство в коллекции.
     *
     * @param string|int $property Свойство для проверки на существование.
     *
     * @return bool True, если свойство существует, false в противном случае.
     */
    public function exists(string|int $property): bool
    {
        return array_key_exists($property, $this->collection);
    }

    /**
     * Метод isEmpty проверяет, пуста ли коллекция.
     *
     * @return bool True, если коллекция пуста, false в противном случае.
     */
    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    /**
     * Метод count возвращает количество элементов в коллекции.
     *
     * @return int Количество элементов в коллекции.
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Метод properties возвращает массив, содержащий имена всех свойств в коллекции.
     *
     * @return array Массив имен всех свойств в коллекции.
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * Метод values возвращает массив, содержащий все значения в коллекции.
     *
     * @return array Массив всех значений в коллекции.
     */
    public function values(): array
    {
        return array_values($this->collection);
    }
}
