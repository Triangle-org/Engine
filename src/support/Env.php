<?php

namespace support;

use Closure;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use PhpOption\Option;
use PhpOption\Some;
use RuntimeException;

class Env
{
    /**
     * Указывает, включен ли адаптер putenv.
     *
     * @var bool
     */
    protected static bool $putenv = true;

    /**
     * Экземпляр репозитория окружения.
     *
     * @var RepositoryInterface|null
     */
    protected static ?RepositoryInterface $repository = null;

    /**
     * Включить адаптер putenv.
     *
     * @return void
     */
    public static function enablePutenv(): void
    {
        self::$putenv = true;
        self::$repository = null;
    }

    /**
     * Отключить адаптер putenv.
     *
     * @return void
     */
    public static function disablePutenv(): void
    {
        self::$putenv = false;
        self::$repository = null;
    }

    /**
     * Получить экземпляр репозитория окружения.
     *
     * @return RepositoryInterface|null
     */
    public static function getRepository(): ?RepositoryInterface
    {
        if (self::$repository === null) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();

            if (self::$putenv) {
                $builder = $builder->addAdapter(PutenvAdapter::class);
            }

            self::$repository = $builder->immutable()->make();
        }

        return self::$repository;
    }

    /**
     * Получить значение переменной окружения.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getOption($key)->getOrCall(fn() => $default instanceof Closure ? $default() : $default);
    }

    /**
     * Получить значение обязательной переменной окружения.
     *
     * @param string $key
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function getOrFail(string $key): mixed
    {
        return self::getOption($key)->getOrThrow(new RuntimeException("Переменная окружения [$key] не имеет значения."));
    }

    /**
     * Получить возможный вариант для этой переменной окружения.
     *
     * @param string $key
     * @return Option|Some
     */
    protected static function getOption(string $key): Some|Option
    {

        return Option::fromValue(self::getRepository()->get($key))
            ->map(function ($value) {
                $valueMap = [
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'empty' => '',
                ];
                $value = strtolower($value);

                if (isset($valueMap[$value]) || isset($valueMap["($value)"])) {
                    return $valueMap[$value] ?? $valueMap["($value)"];
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            });
    }
}