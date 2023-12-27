<?php

namespace support;

/**
 * Гибкая коллекция данных.
 */
final class Collection
{
    /**
     * Коллекция данных
     *
     * @var array
     */
    protected array $collection = [];

    /**
     * Создает новую коллекцию.
     *
     * @param mixed $data Начальные данные для коллекции, которые будут приведены к массиву.
     */
    public function __construct(mixed $data = null)
    {
        $this->collection = (array)$data;
    }

    /**
     * Получает всю коллекцию в виде массива
     *
     * @return array Вся коллекция данных в виде массива.
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * Получает элемент
     *
     * @param string $property Имя свойства для получения.
     *
     * @return mixed Значение свойства или null, если его не существует.
     */
    public function get(string $property): mixed
    {
        return $this->collection[$property] ?? null;
    }

    /**
     * Добавляет или обновляет элемент
     *
     * @param string $property Имя свойства для установки.
     * @param mixed $value Значение для установки.
     */
    public function set(string $property, mixed $value): void
    {
        $this->collection[$property] = $value;
    }

    /**
     * Фильтрует коллекцию по свойству.
     *
     * @param string $property Свойство для фильтрации.
     *
     * @return Collection Новая коллекция, содержащая отфильтрованные данные.
     */
    public function filter(string $property): Collection
    {
        return new Collection(array_filter($this->collection, function ($key) use ($property) {
            return $key === $property;
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Проверяет, существует ли элемент в коллекции
     *
     * @param string $property Свойство для проверки на существование.
     *
     * @return bool True, если свойство существует, false в противном случае.
     */
    public function exists(string $property): bool
    {
        return array_key_exists($property, $this->collection);
    }

    /**
     * Определяет, пуста ли коллекция
     *
     * @return bool True, если коллекция пуста, false в противном случае.
     */
    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    /**
     * Считает все элементы в коллекции
     *
     * @return int Количество элементов в коллекции.
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Возвращает все имена свойств элементов
     *
     * @return array Массив имен всех свойств в коллекции.
     */
    public function properties(): array
    {
        return array_keys($this->collection);
    }

    /**
     * Возвращает все значения элементов
     *
     * @return array Массив всех значений в коллекции.
     */
    public function values(): array
    {
        return array_values($this->collection);
    }
}
