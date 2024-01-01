<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Localzet Group
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

namespace Triangle\Engine\Router;

use Triangle\Engine\Router;
use function array_merge;
use function count;
use function preg_replace_callback;
use function str_replace;


/**
 * Класс Route
 * Этот класс представляет собой маршрут, который управляет маршрутами приложения.
 */
class Route
{
    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var array
     */
    protected array $methods = [];

    /**
     * @var string
     */
    protected string $path = '';

    /**
     * @var callable
     */
    protected $callback = null;

    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * Конструктор маршрута.
     * @param array $methods HTTP-методы
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     */
    public function __construct(array $methods, string $path, mixed $callback)
    {
        $this->methods = $methods;
        $this->path = $path;
        $this->callback = $callback;
    }

    /**
     * Получить имя маршрута
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Установить имя маршрута
     * @param string $name Имя маршрута
     * @return $this
     */
    public function name(string $name): Route
    {
        $this->name = $name;
        Router::setByName($name, $this);
        return $this;
    }

    /**
     * Получить или установить промежуточное ПО
     * @param mixed|null $middleware Промежуточное ПО
     * @return $this|array
     */
    public function middleware(mixed $middleware = null): array|static
    {
        if ($middleware === null) {
            return $this->middlewares;
        }
        $this->middlewares = array_merge($this->middlewares, is_array($middleware) ? array_reverse($middleware) : [$middleware]);
        return $this;
    }

    /**
     * Получить путь маршрута
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Получить методы маршрута
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Получить обработчик маршрута
     * @return callable|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * Получить промежуточное ПО маршрута
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    /**
     * Получить параметры маршрута
     * @param string|null $name Имя параметра
     * @param $default Значение по умолчанию
     * @return array|mixed|null
     */
    public function param(string $name = null, $default = null): mixed
    {
        if ($name === null) {
            return $this->params;
        }
        return $this->params[$name] ?? $default;
    }

    /**
     * Установить параметры маршрута
     * @param array $params Параметры
     * @return $this
     */
    public function setParams(array $params): Route
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Получить URL маршрута
     * @param array $parameters Параметры
     * @return string
     */
    public function url(array $parameters = []): string
    {
        if (empty($parameters)) {
            return $this->path;
        }
        $path = str_replace(['[', ']'], '', $this->path);
        $path = preg_replace_callback('/\{(.*?)(?::[^}]*?)*?}/', function ($matches) use (&$parameters) {
            if (!$parameters) {
                return $matches[0];
            }
            if (isset($parameters[$matches[1]])) {
                $value = $parameters[$matches[1]];
                unset($parameters[$matches[1]]);
                return $value;
            }
            $key = key($parameters);
            if (is_int($key)) {
                $value = $parameters[$key];
                unset($parameters[$key]);
                return $value;
            }
            return $matches[0];
        }, $path);
        return count($parameters) > 0 ? $path . '?' . http_build_query($parameters) : $path;
    }
}
