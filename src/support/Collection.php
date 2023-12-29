<?php

namespace support;

/**
 * Класс Collection представляет собой гибкую коллекцию данных.
 * Это означает, что он может хранить различные типы данных, такие как строки, числа, массивы и объекты.
 */
final class Collection
{
    /**
     * Коллекция данных.
     * Это свойство хранит все данные, которые были добавлены в коллекцию.
     *
     * @var object|null $collection
     */
    protected ?object $collection = null;

    /**
     * Конструктор класса Collection.
     * Этот метод принимает необязательный аргумент $data, который будет приведен к объекту и сохранен в свойстве $collection.
     *
     * @param mixed $data Начальные данные для коллекции, которые будут приведены к массиву.
     */
    public function __construct(mixed $data = null)
    {
        $this->collection = (object)$data;
    }

    /**
     * Метод toArray возвращает всю коллекцию данных в виде массива.
     *
     * @return array Вся коллекция данных в виде массива.
     */
    public function toArray(): array
    {
        return (array)$this->collection;
    }

    /**
     * Метод get возвращает значение указанного свойства из коллекции.
     * Если свойство не существует, метод возвращает null.
     *
     * @param string $property Имя свойства для получения.
     *
     * @return mixed Значение свойства или null, если его не существует.
     */
    public function get(string $property): mixed
    {
        if ($this->exists($property)) {
            return $this->collection->$property;
        }

        return null;
    }

    /**
     * Метод set добавляет новое свойство в коллекцию или обновляет существующее.
     *
     * @param string $property Имя свойства для установки.
     * @param mixed $value Значение для установки.
     */
    public function set(string $property, mixed $value): void
    {
        if ($property) {
            $this->collection->$property = $value;
        }
    }

    /**
     * Метод filter возвращает новую коллекцию, которая содержит только элементы, соответствующие указанному свойству.
     * Если свойство не существует, метод возвращает пустую коллекцию.
     *
     * @param string $property Свойство для фильтрации.
     *
     * @return Collection Новая коллекция, содержащая отфильтрованные данные.
     */
    public function filter(string $property): Collection
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
     * Метод exists проверяет, существует ли указанное свойство в коллекции.
     *
     * @param string $property Свойство для проверки на существование.
     *
     * @return bool True, если свойство существует, false в противном случае.
     */
    public function exists(string $property): bool
    {
        return property_exists($this->collection, $property);
    }

    /**
     * Метод isEmpty проверяет, пуста ли коллекция.
     *
     * @return bool True, если коллекция пуста, false в противном случае.
     */
    public function isEmpty(): bool
    {
        return !(bool)$this->count();
    }

    /**
     * Метод count возвращает количество элементов в коллекции.
     *
     * @return int Количество элементов в коллекции.
     */
    public function count(): int
    {
        return count($this->properties());
    }

    /**
     * Метод properties возвращает массив, содержащий имена всех свойств в коллекции.
     *
     * @return array Массив имен всех свойств в коллекции.
     */
    public function properties(): array
    {
        $properties = [];

        foreach ($this->collection as $key => $value) {
            $properties[] = $key;
        }

        return $properties;
    }

    /**
     * Метод values возвращает массив, содержащий все значения в коллекции.
     *
     * @return array Массив всех значений в коллекции.
     */
    public function values(): array
    {
        $values = [];

        foreach ($this->collection as $value) {
            $values[] = $value;
        }

        return $values;
    }
}
