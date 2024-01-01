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

namespace Triangle\Engine;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Triangle\Engine\Router\Route as RouteObject;
use function array_diff;
use function array_values;
use function class_exists;
use function explode;
use function FastRoute\simpleDispatcher;
use function in_array;
use function is_array;
use function is_callable;
use function is_file;
use function is_scalar;
use function is_string;
use function json_encode;
use function method_exists;
use function strpos;

/**
 * Класс Router
 * Этот класс представляет собой маршрутизатор, который управляет маршрутами вашего приложения.
 */
class Router
{
    /**
     * @var Router|null
     */
    protected static ?Router $instance = null;

    /**
     * @var Dispatcher|null
     */
    protected static ?Dispatcher $dispatcher = null;

    /**
     * @var RouteCollector|null
     */
    protected static ?RouteCollector $collector = null;

    /**
     * @var null|callable
     */
    protected static $fallback = [];

    /**
     * @var array
     */
    protected static array $nameList = [];

    /**
     * @var string
     */
    protected static string $groupPrefix = '';

    /**
     * @var bool
     */
    protected static array|bool $disableDefaultRoute = [];

    /**
     * @var RouteObject[]
     */
    protected static array $allRoutes = [];

    /**
     * @var RouteObject[]
     */
    protected array $routes = [];

    /**
     * @var Router[]
     */
    protected array $children = [];

    /**
     * Добавить маршрут PATCH
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function patch(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('PATCH', $path, $callback);
    }

    /**
     * Добавить маршрут
     * @param array|string $methods HTTP-методы
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    protected static function addRoute(array|string $methods, string $path, mixed $callback): RouteObject
    {
        $route = new RouteObject((is_array($methods) ? $methods : [$methods]), static::$groupPrefix . $path, $callback);
        static::$allRoutes[] = $route;

        if ($callback = static::convertToCallable($path, $callback)) {
            static::$collector->addRoute($methods, $path, ['callback' => $callback, 'route' => $route]);
        }
        static::$instance?->collect($route);
        return $route;
    }

    /**
     * @param Router $route
     * @return void
     */
    public function addChild(Router $route)
    {
        $this->children[] = $route;
    }

    /**
     * @return Router[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Преобразовать обработчик в вызываемую функцию
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return callable|false|string[]
     */
    public static function convertToCallable(string $path, mixed $callback): array|callable|false
    {
        if (is_string($callback) && strpos($callback, '@')) {
            $callback = explode('@', $callback, 2);
        }

        if (!is_array($callback)) {
            if (!is_callable($callback)) {
                $callStr = is_scalar($callback) ? $callback : 'Closure';
                echo "Router $path $callStr is not callable\n";
                return false;
            }
        } else {
            $callback = array_values($callback);
            if (!isset($callback[1]) || !class_exists($callback[0]) || !method_exists($callback[0], $callback[1])) {
                echo "Router $path " . json_encode($callback) . " is not callable\n";
                return false;
            }
        }

        return $callback;
    }

    /**
     * Собрать маршрут
     * @param RouteObject $route Объект маршрута
     */
    public function collect(RouteObject $route): void
    {
        $this->routes[] = $route;
    }

    /**
     * Добавить маршрут HEAD
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function head(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('HEAD', $path, $callback);
    }

    /**
     * Добавить маршрут OPTIONS
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */

    public static function options(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('OPTIONS', $path, $callback);
    }

    /**
     * Добавить маршрут
     * @param array|string $method HTTP-метод
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function add(array|string $method, string $path, mixed $callback): RouteObject
    {
        return static::addRoute($method, $path, $callback);
    }

    /**
     * Добавить группу маршрутов
     * @param callable|string $path Путь группы
     * @param callable|null $callback Обработчик группы
     * @return static
     */
    public static function group(callable|string $path, callable $callback = null): Router
    {
        if ($callback === null) {
            $callback = $path;
            $path = '';
        }

        $previousGroupPrefix = static::$groupPrefix;
        static::$groupPrefix = $previousGroupPrefix . $path;
        $previousInstance = static::$instance;
        $instance = static::$instance = new static;
        static::$collector->addGroup($path, $callback);
        static::$groupPrefix = $previousGroupPrefix;
        static::$instance = $previousInstance;
        $previousInstance?->addChild($instance);
        return $instance;
    }

    /**
     * Добавить ресурсный маршрут
     * @param string $name Имя ресурса
     * @param string $controller Контроллер ресурса
     * @param array $options Опции ресурса
     * @param string $prefix Префикс для имени
     * @return void
     */
    public static function resource(string $name, string $controller, array $options = [], string $prefix = ''): void
    {
        $name = trim($name, '/');
        if (is_array($options) && !empty($options)) {
            $diffOptions = array_diff($options, ['index', 'create', 'store', 'update', 'show', 'edit', 'destroy', 'recovery']);
            if (!empty($diffOptions)) {
                foreach ($diffOptions as $action) {
                    static::any("/$name/{$action}[/{id}]", [$controller, $action])->name("$prefix$name.$action");
                }
            }

            // Отображение списка ресурсов
            if (in_array('index', $options)) static::get("/$name", [$controller, 'index'])->name("$prefix$name.index");

            // Отображение конкретного ресурса
            if (in_array('show', $options)) static::get("/$name/{id}", [$controller, 'show'])->name("$prefix$name.show");


            // Отображение формы для создания нового ресурса
            if (in_array('create', $options)) static::get("/$name/create", [$controller, 'create'])->name("$prefix$name.create");

            // Создание нового ресурса
            if (in_array('store', $options)) static::post("/$name", [$controller, 'store'])->name("$prefix$name.store");


            // Отображение формы для обновления существующего ресурса
            if (in_array('edit', $options)) static::get("/$name/{id}/edit", [$controller, 'edit'])->name("$prefix$name.edit");

            // Обновление существующего ресурса
            if (in_array('update', $options)) static::put("/$name/{id}", [$controller, 'update'])->name("$prefix$name.update");


            // Удаление существующего ресурса
            if (in_array('destroy', $options)) static::delete("/$name/{id}", [$controller, 'destroy'])->name("$prefix$name.destroy");
        } else {
            // Отображение списка ресурсов
            if (method_exists($controller, 'index')) static::get("/$name", [$controller, 'index'])->name("$prefix$name.index");

            // Отображение конкретного ресурса
            if (method_exists($controller, 'show')) static::get("/$name/{id}", [$controller, 'show'])->name("$prefix$name.show");


            // Отображение формы для создания нового ресурса
            if (method_exists($controller, 'create')) static::get("/$name/create", [$controller, 'create'])->name("$prefix$name.create");

            // Создание нового ресурса
            if (method_exists($controller, 'store')) static::post("/$name", [$controller, 'store'])->name("$prefix$name.store");


            // Отображение формы для обновления существующего ресурса
            if (method_exists($controller, 'edit')) static::get("/$name/{id}/edit", [$controller, 'edit'])->name("$prefix$name.edit");

            // Обновление существующего ресурса
            if (method_exists($controller, 'update')) static::put("/$name/{id}", [$controller, 'update'])->name("$prefix$name.update");


            // Удаление существующего ресурса
            if (method_exists($controller, 'destroy')) static::delete("/$name/{id}", [$controller, 'destroy'])->name("$prefix$name.destroy");
        }
    }

    /**
     * Добавить маршрут ANY
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function any(string $path, mixed $callback): RouteObject
    {
        return static::addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'], $path, $callback);
    }

    /**
     * Добавить маршрут GET
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function get(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('GET', $path, $callback);
    }

    /**
     * Добавить маршрут POST
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function post(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('POST', $path, $callback);
    }

    /**
     * Добавить маршрут PUT
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function put(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('PUT', $path, $callback);
    }

    /**
     * Добавить маршрут DELETE
     * @param string $path Путь маршрута
     * @param callable|mixed $callback Обработчик маршрута
     * @return RouteObject
     */
    public static function delete(string $path, mixed $callback): RouteObject
    {
        return static::addRoute('DELETE', $path, $callback);
    }

    /**
     * Получить все маршруты
     * @return RouteObject[]
     */
    public static function getRoutes(): array
    {
        return static::$allRoutes;
    }

    /**
     * Отключить маршрут по умолчанию
     * @param string $plugin Имя плагина
     * @return void
     */
    public static function disableDefaultRoute(string $plugin = ''): void
    {
        static::$disableDefaultRoute[$plugin] = true;
    }

    /**
     * Проверить, отключен ли маршрут по умолчанию
     * @param string $plugin Имя плагина
     * @return bool
     */
    public static function hasDisableDefaultRoute(string $plugin = ''): bool
    {
        return static::$disableDefaultRoute[$plugin] ?? false;
    }

    /**
     * Установить маршрут по имени
     * @param string $name Имя маршрута
     * @param RouteObject $instance Экземпляр маршрута
     */
    public static function setByName(string $name, RouteObject $instance): void
    {
        static::$nameList[$name] = $instance;
    }

    /**
     * Получить маршрут по имени
     * @param string $name Имя маршрута
     * @return null|RouteObject
     */
    public static function getByName(string $name): ?RouteObject
    {
        return static::$nameList[$name] ?? null;
    }

    /**
     * Выполнить маршрут
     * @param string $method HTTP-метод
     * @param string $path Путь маршрута
     * @return array
     */
    public static function dispatch(string $method, string $path): array
    {
        return static::$dispatcher->dispatch($method, $path);
    }

    /**
     * Загрузить маршруты
     * @param mixed $paths Пути к файлам конфигурации маршрутов
     * @return void
     */
    public static function load(mixed $paths): void
    {
        if (!is_array($paths)) {
            return;
        }
        static::$dispatcher = simpleDispatcher(function (RouteCollector $route) use ($paths) {
            Router::setCollector($route);
            foreach ($paths as $configPath) {
                $routeConfigFile = $configPath . '/route.php';
                if (is_file($routeConfigFile)) {
                    require_once $routeConfigFile;
                }
                if (!is_dir($pluginConfigPath = $configPath . '/plugin')) {
                    continue;
                }
                $dirIterator = new RecursiveDirectoryIterator($pluginConfigPath, FilesystemIterator::FOLLOW_SYMLINKS);
                $iterator = new RecursiveIteratorIterator($dirIterator);
                foreach ($iterator as $file) {
                    if ($file->getBaseName('.php') !== 'route') {
                        continue;
                    }
                    $appConfigFile = pathinfo($file, PATHINFO_DIRNAME) . '/app.php';
                    if (!is_file($appConfigFile)) {
                        continue;
                    }
                    $appConfig = include $appConfigFile;
                    if (empty($appConfig['enable'])) {
                        continue;
                    }
                    require_once $file;
                }
            }
        });
    }

    /**
     * Установить сборщик маршрутов
     * @param RouteCollector $route Сборщик маршрутов
     * @return void
     */
    public static function setCollector(RouteCollector $route): void
    {
        static::$collector = $route;
    }

    /**
     * Fallback.
     * @param callable|mixed $callback
     * @param string $plugin
     * @return void
     */
    public static function fallback(callable $callback, string $plugin = ''): void
    {
        static::$fallback[$plugin] = $callback;
    }

    /**
     * GetFallBack.
     * @param string $plugin
     * @return callable|null
     */
    public static function getFallback(string $plugin = ''): ?callable
    {
        return static::$fallback[$plugin] ?? null;
    }

    /**
     * @param $middleware
     * @return $this
     */
    public function middleware($middleware): Router
    {
        foreach ($this->routes as $route) {
            $route->middleware($middleware);
        }
        foreach ($this->getChildren() as $child) {
            $child->middleware($middleware);
        }
        return $this;
    }
}
