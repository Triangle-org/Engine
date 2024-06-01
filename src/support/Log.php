<?php

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

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use function array_values;
use function config;
use function is_array;

/**
 * Класс Log
 * Этот класс предоставляет статические методы для работы с логированием.
 *
 * @link https://www.php-fig.org/psr/psr-3/
 *
 * Методы:
 * @method static void log($level, $message, array $context = []) Записывает лог на указанном уровне.
 * @method static void debug($message, array $context = []) Записывает отладочный лог.
 * @method static void info($message, array $context = []) Записывает информационный лог.
 * @method static void notice($message, array $context = []) Записывает лог уровня "notice".
 * @method static void warning($message, array $context = []) Записывает предупреждающий лог.
 * @method static void error($message, array $context = []) Записывает лог ошибки.
 * @method static void critical($message, array $context = []) Записывает критический лог.
 * @method static void alert($message, array $context = []) Записывает лог уровня "alert".
 * @method static void emergency($message, array $context = []) Записывает лог уровня "emergency".
 */
class Log
{
    /**
     * @var array $instance Экземпляры логгера для каждого канала.
     */
    protected static array $instance = [];

    /**
     * Магический метод для вызова методов логгера.
     *
     * @param string $name Имя метода.
     * @param array $arguments Аргументы метода.
     * @return mixed Результат вызова метода.
     *
     * @link https://www.php.net/manual/ru/language.oop5.overloading.php#object.callstatic
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::channel()->{$name}(...$arguments);
    }

    /**
     * Метод для получения экземпляра логгера.
     *
     * @param string $name Имя канала логгера.
     * @param array $localConfig Локальная конфигурация логгера.
     * @return Logger Экземпляр логгера.
     *
     * @link https://github.com/Seldaek/monolog
     */
    public static function channel(string $name = 'default', array $localConfig = []): Logger
    {
        if (!isset(static::$instance[$name])) {
            $config = config("log.$name", $localConfig);
            $handlers = self::handlers($config);
            $processors = self::processors($config);
            static::$instance[$name] = new Logger($name, $handlers, $processors);
        }

        return static::$instance[$name];
    }

    /**
     * Метод для создания обработчиков логгера.
     *
     * @param array $config Конфигурация обработчиков.
     * @return array Список обработчиков.
     *
     * @link https://github.com/Seldaek/monolog
     */
    protected static function handlers(array $config): array
    {
        $handlerConfigs = $config['handlers'] ?? [[]];
        $handlers = [];
        foreach ($handlerConfigs as $value) {
            $class = $value['class'] ?? [];
            $constructor = $value['constructor'] ?? [];
            $formatter = $value['formatter'] ?? [];

            $class && $handlers[] = self::handler($class, $constructor, $formatter);
        }

        return $handlers;
    }

    /**
     * Метод для создания обработчика логгера.
     *
     * @param string $class Класс обработчика.
     * @param array $constructor Конструктор обработчика.
     * @param array $formatterConfig Конфигурация форматтера.
     * @return HandlerInterface Обработчик логгера.
     *
     * @link https://github.com/Seldaek/monolog
     */
    protected static function handler(string $class, array $constructor, array $formatterConfig): HandlerInterface
    {
        /** @var HandlerInterface $handler */
        $handler = new $class(...array_values($constructor));

        if ($handler instanceof FormattableHandlerInterface && $formatterConfig) {
            $formatterClass = $formatterConfig['class'];
            $formatterConstructor = $formatterConfig['constructor'];

            /** @var FormatterInterface $formatter */
            $formatter = new $formatterClass(...array_values($formatterConstructor));

            $handler->setFormatter($formatter);
        }

        return $handler;
    }

    /**
     * Метод для создания процессоров логгера.
     *
     * @param array $config Конфигурация процессоров.
     * @return array Список процессоров.
     *
     * @link https://github.com/Seldaek/monolog
     */
    protected static function processors(array $config): array
    {
        $result = [];
        if (!isset($config['processors']) && isset($config['processor'])) {
            $config['processors'] = [$config['processor']];
        }

        foreach ($config['processors'] ?? [] as $value) {
            if (is_array($value) && isset($value['class'])) {
                $value = new $value['class'](...array_values($value['constructor'] ?? []));
            }
            $result[] = $value;
        }

        return $result;
    }
}
