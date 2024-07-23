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

namespace Triangle\Engine;

use Closure;
use ErrorException;
use Exception;
use FastRoute\Dispatcher;
use InvalidArgumentException;
use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use localzet\Server\Protocols\Http;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;
use Triangle\Engine\Autoload\BootstrapLoader;
use Triangle\Engine\Autoload\EventLoader;
use Triangle\Engine\Autoload\FileLoader;
use Triangle\Engine\Autoload\MiddlewareLoader;
use Triangle\Engine\Exception\ExceptionHandler;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Interface\ExceptionHandlerInterface;
use Triangle\Engine\Interface\MiddlewareInterface;
use Triangle\Engine\Router\Route as RouteObject;
use function array_merge;
use function array_pop;
use function array_reduce;
use function array_splice;
use function array_values;
use function class_exists;
use function clearstatcache;
use function count;
use function current;
use function end;
use function explode;
use function get_class_methods;
use function gettype;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function key;
use function method_exists;
use function next;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function scandir;
use function str_replace;
use function strtolower;
use function substr;
use function trim;

/**
 * Class App
 */
class App
{
    /**
     * @var callable[]
     */
    protected static array $callbacks = [];

    /**
     * @var Server|null
     */
    protected static ?Server $server = null;

    /**
     * @var Logger|null
     */
    protected static ?Logger $logger = null;


    /**
     * @var string|null
     */
    protected static ?string $requestClass = null;

    /**
     * @param string $requestClass
     * @param Logger $logger
     * @param string|null $basePath
     * @param string|null $appPath
     * @param string|null $configPath
     * @param string|null $publicPath
     * @param string|null $runtimePath
     */
    public function __construct(
        string $requestClass,
        Logger $logger,
        string $basePath = null,
        string $appPath = null,
        string $configPath = null,
        string $publicPath = null,
        string $runtimePath = null,
    )
    {
        static::$requestClass = $requestClass;
        static::$logger = $logger;

        new Path(
            $basePath,
            $appPath,
            $configPath,
            $publicPath,
            $runtimePath
        );
    }

    /**
     * @return TcpConnection|null
     */
    public static function connection(): TcpConnection|null
    {
        return Context::get(TcpConnection::class);
    }

    /**
     * @return Request|null
     */
    public static function request(): Request|null
    {
        return Context::get(Request::class);
    }

    /**
     * @return Server|null
     */
    public static function server(): ?Server
    {
        return static::$server;
    }

    /**
     * Функция для обработки сообщений.
     *
     * @param mixed $connection Соединение TCP.
     * @param mixed $request Запрос.
     * @return null
     * @throws Throwable
     */
    public function onMessage(mixed $connection, mixed $request): null
    {
        try {
            // Устанавливаем контекст для соединения и запроса
            Context::set(TcpConnection::class, $connection);
            Context::set(Request::class, $request);

            // Получаем путь из запроса
            $path = $request->path();

            // Создаем ключ из метода запроса и пути
            $key = $request->method() . $path;

            // Если для данного ключа уже есть обратные вызовы
            if (isset(static::$callbacks[$key])) {
                // Получаем обратные вызовы
                [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];

                // Отправляем обратный вызов
                static::send($connection, $callback($request), $request);
                return null;
            }

            // Проверяем на небезопасные URI, находим файл или маршрут
            if (
                static::unsafeUri($connection, $path, $request) ||
                static::findFile($connection, $path, $key, $request) ||
                static::findRoute($connection, $path, $key, $request)
            ) {
                return null;
            }

            // Парсим контроллер и действие из пути
            $controllerAndAction = static::parseControllerAction($path);

            // Получаем плагин по пути или из контроллера и действия
            $plugin = $controllerAndAction['plugin'] ?? static::getPluginByPath($path);

            // Если контроллер и действие не найдены или маршрут по умолчанию отключен
            if (!$controllerAndAction || Router::hasDisableDefaultRoute($plugin)) {
                // Устанавливаем плагин в запросе
                $request->plugin = $plugin;

                // Получаем обратный вызов для отката
                $callback = static::getFallback($plugin);

                // Устанавливаем приложение, контроллер и действие в запросе
                $request->app = $request->controller = $request->action = '';

                // Отправляем обратный вызов
                static::send($connection, $callback($request), $request);
                return null;
            }

            // Получаем приложение, контроллер и действие
            $app = $controllerAndAction['app'];
            $controller = $controllerAndAction['controller'];
            $action = $controllerAndAction['action'];

            // Получаем обратный вызов
            $callback = static::getCallback($plugin, $app, [$controller, $action]);

            // Собираем обратные вызовы
            static::collectCallbacks($key, [$callback, $plugin, $app, $controller, $action, null]);

            // Получаем обратные вызовы
            [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];

            // Отправляем обратный вызов
            static::send($connection, $callback($request), $request);
        } catch (Throwable $e) {
            // Если возникло исключение, отправляем ответ на исключение
            static::send($connection, static::exceptionResponse($e, $request), $request);
        }
        return null;
    }

    /**
     * @param TcpConnection|mixed $connection
     * @param mixed $response
     * @param Request|mixed $request
     * @return void
     * @throws Throwable
     */
    protected static function send(mixed $connection, mixed $response, mixed $request): void
    {
        $keepAlive = $request->header('connection');
        Context::destroy();
        if (($keepAlive === null && $request->protocolVersion() === '1.1')
            || $keepAlive === 'keep-alive' || $keepAlive === 'Keep-Alive'
        ) {
            $connection->send($response);
            return;
        }
        $connection->close($response);
    }

    /**
     * @param TcpConnection $connection
     * @param string $path
     * @param $request
     * @return bool
     * @throws Throwable
     */
    protected static function unsafeUri(TcpConnection $connection, string $path, $request): bool
    {
        if (
            !$path ||
            str_contains($path, '..') ||
            str_contains($path, "\\") ||
            str_contains($path, "\0")
        ) {
            $callback = static::getFallback();
            $request->plugin = $request->app = $request->controller = $request->action = '';
            static::send($connection, $callback($request), $request);
            return true;
        }
        return false;
    }

    /**
     * @param string $plugin
     * @return Closure
     */
    protected static function getFallback(string $plugin = ''): Closure
    {
        // when route, controller and action not found, try to use Router::fallback
        return Router::getFallback($plugin) ?: function () {
            return not_found();
        };
    }

    /**
     * Функция для поиска файла.
     *
     * @param TcpConnection $connection Соединение TCP.
     * @param string $path Путь.
     * @param string $key Ключ.
     * @param mixed $request Запрос.
     * @return bool Возвращает true, если файл найден, иначе возвращает false.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Throwable
     */
    protected static function findFile(TcpConnection $connection, string $path, string $key, mixed $request): bool
    {
        // Если в пути есть процентное кодирование, декодируем его
        if (preg_match('/%[0-9a-f]{2}/i', $path)) {
            $path = urldecode($path);
            if (static::unsafeUri($connection, $path, $request)) {
                return true;
            }
        }

        // Разбиваем путь на части
        $pathExplodes = explode('/', trim($path, '/'));
        $plugin = '';

        // Если путь указывает на плагин
        if (isset($pathExplodes[1]) && $pathExplodes[0] === 'app') {
            $publicDir = static::config($plugin, 'app.public_path') ?: Path::basePath("plugin/$pathExplodes[1]/public");
            $plugin = $pathExplodes[1];
            $path = substr($path, strlen("/app/$pathExplodes[1]/"));
        } else {
            // Иначе используем общедоступную директорию
            $publicDir = Path::publicPath();
        }

        // Получаем полный путь к файлу
        $file = "$publicDir/$path";

        // Если файл не существует, возвращаем false
        if (!is_file($file)) {
            return false;
        }

        // Если файл является PHP-файлом
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            // Если PHP-файлы не поддерживаются, возвращаем false
            if (!static::config($plugin, 'app.support_php_files', false)) {
                return false;
            }

            // Добавляем обратный вызов для выполнения PHP-файла
            static::collectCallbacks($key, [function () use ($file) {
                return static::execPhpFile($file);
            }, '', '', '', '', null]);

            // Получаем обратные вызовы
            [, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];

            // Отправляем обратный вызов
            static::send($connection, static::execPhpFile($file), $request);
            return true;
        }

        // Если статические файлы не поддерживаются, возвращаем false
        if (!static::config($plugin, 'static.enable', false)) {
            return false;
        }

        // Добавляем обратный вызов для отправки файла
        static::collectCallbacks($key, [static::getCallback($plugin, '__static__', function ($request) use ($file, $plugin) {
            clearstatcache(true, $file);
            if (!is_file($file)) {
                $callback = static::getFallback($plugin);
                return $callback($request);
            }
            return (new Response())->file($file);
        }, null, false), '', '', '', '', null]);
        // Получаем обратные вызовы
        [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
        // Отправляем обратный вызов
        static::send($connection, $callback($request), $request);
        return true;
    }

    /**
     * Конфигурация
     * @param string $plugin
     * @param string $key
     * @param $default
     * @return array|mixed|null
     */
    protected static function config(string $plugin, string $key, $default = null): mixed
    {
        return Config::get($plugin ? "plugin.$plugin.$key" : $key, $default);
    }

    /**
     * @param string $key
     * @param array $data
     * @return void
     */
    protected static function collectCallbacks(string $key, array $data): void
    {
        static::$callbacks[$key] = $data;
        if (count(static::$callbacks) >= 1024) {
            unset(static::$callbacks[key(static::$callbacks)]);
        }
    }

    /**
     * Выполнить php файл
     * @param string $file
     * @return false|string
     */
    public static function execPhpFile(string $file): false|string
    {
        ob_start();
        // Try to include php file.
        try {
            include $file;
        } catch (Exception $e) {
            echo $e;
        }
        return ob_get_clean();
    }

    /**
     * Функция для получения обратного вызова.
     *
     * @param string $plugin Плагин.
     * @param string $app Приложение.
     * @param mixed $call Вызов.
     * @param array|null $args Аргументы.
     * @param bool $withGlobalMiddleware Использовать глобальное промежуточное ПО.
     * @param RouteObject|null $route Маршрут.
     * @return callable|Closure Возвращает обратный вызов.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected static function getCallback(string $plugin, string $app, mixed $call, array $args = null, bool $withGlobalMiddleware = true, RouteObject $route = null): callable|Closure
    {
        $args = $args === null ? null : array_values($args);
        $middlewares = [];
        // Если есть маршрут, получаем промежуточное ПО маршрута
        if ($route) {
            $routeMiddlewares = $route->getMiddleware();
            foreach ($routeMiddlewares as $className) {
                $middlewares[] = [$className, 'process'];
            }
        }
        // Добавляем глобальное промежуточное ПО
        $middlewares = array_merge($middlewares, MiddlewareLoader::getMiddleware($plugin, $app, $withGlobalMiddleware));

        // Создаем экземпляры промежуточного ПО
        foreach ($middlewares as $key => $item) {
            $middleware = $item[0];
            if (is_string($middleware)) {
                $middleware = static::container($plugin)->get($middleware);
            } elseif ($middleware instanceof Closure) {
                $middleware = call_user_func($middleware, static::container($plugin));
            }
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException('Not support middleware type');
            }
            $middlewares[$key][0] = $middleware;
        }

        // Проверяем, нужно ли внедрять зависимости в вызов
        $needInject = static::isNeedInject($call, $args);
        if (is_array($call) && is_string($call[0])) {
            $controllerReuse = static::config($plugin, 'app.controller_reuse', true);
            if (!$controllerReuse) {
                if ($needInject) {
                    $call = function ($request, ...$args) use ($call, $plugin) {
                        $call[0] = static::container($plugin)->make($call[0]);
                        $reflector = static::getReflector($call);
                        $args = static::resolveMethodDependencies($plugin, $request, $args, $reflector);
                        return $call(...$args);
                    };
                    $needInject = false;
                } else {
                    $call = function ($request, ...$args) use ($call, $plugin) {
                        $call[0] = static::container($plugin)->make($call[0]);
                        return $call($request, ...$args);
                    };
                }
            } else {
                $call[0] = static::container($plugin)->get($call[0]);
            }
        }

        // Если нужно внедрить зависимости, внедряем их
        if ($needInject) {
            $call = static::resolveInject($plugin, $call);
        }

        // Если есть промежуточное ПО, создаем цепочку вызовов
        if ($middlewares) {
            $callback = array_reduce($middlewares, function ($carry, $pipe) {
                return function ($request) use ($carry, $pipe) {
                    try {
                        return $pipe($request, $carry);
                    } catch (Throwable $e) {
                        return static::exceptionResponse($e, $request);
                    }
                };
            }, function ($request) use ($call, $args) {
                try {
                    if ($args === null) {
                        $response = $call($request);
                    } else {
                        $response = $call($request, ...$args);
                    }
                } catch (Throwable $e) {
                    return static::exceptionResponse($e, $request);
                }
                if (!$response instanceof Response) {
                    if (!is_string($response)) {
                        $response = static::stringify($response);
                    }
                    $response = new Response(200, [], $response);
                }
                return $response;
            });
        } else {
            // Если нет промежуточного ПО, создаем обратный вызов
            if ($args === null) {
                $callback = $call;
            } else {
                $callback = function ($request) use ($call, $args) {
                    return $call($request, ...$args);
                };
            }
        }
        return $callback;
    }


    /**
     * @param string $plugin
     * @return ContainerInterface|array|null
     */
    public static function container(string $plugin = ''): ContainerInterface|array|null
    {
        return static::config($plugin, 'container');
    }

    /**
     * Check whether inject is required
     * @param $call
     * @param $args
     * @return bool
     * @throws ReflectionException
     */
    protected static function isNeedInject($call, $args): bool
    {
        if (is_array($call) && !method_exists($call[0], $call[1])) {
            return false;
        }
        $args = $args ?: [];
        $reflector = static::getReflector($call);
        $reflectionParameters = $reflector->getParameters();
        if (!$reflectionParameters) {
            return false;
        }
        $firstParameter = current($reflectionParameters);
        unset($reflectionParameters[key($reflectionParameters)]);
        $adaptersList = ['int', 'string', 'bool', 'array', 'object', 'float', 'mixed', 'resource'];
        foreach ($reflectionParameters as $parameter) {
            if ($parameter->hasType() && !in_array($parameter->getType()->getName(), $adaptersList)) {
                return true;
            }
        }
        if (!$firstParameter->hasType()) {
            return count($args) > count($reflectionParameters);
        }

        if (!is_a(static::$requestClass, $firstParameter->getType()->getName())) {
            return true;
        }

        return false;
    }

    /**
     * Get reflector.
     *
     * @param $call
     * @return ReflectionFunction|ReflectionMethod
     * @throws ReflectionException
     */
    protected static function getReflector($call): ReflectionMethod|ReflectionFunction
    {
        if ($call instanceof Closure || is_string($call)) {
            return new ReflectionFunction($call);
        }
        return new ReflectionMethod($call[0], $call[1]);
    }

    /**
     * Функция для получения зависимых параметров.
     *
     * @param string $plugin Плагин.
     * @param Request $request Запрос.
     * @param array $args Аргументы.
     * @param ReflectionFunctionAbstract $reflector Рефлектор.
     * @return array Возвращает массив с зависимыми параметрами.
     */
    protected static function resolveMethodDependencies(string $plugin, Request $request, array $args, ReflectionFunctionAbstract $reflector): array
    {
        // Спецификация информации о параметрах
        $args = array_values($args);
        $parameters = [];
        // Массив классов рефлексии для циклических параметров, каждый $parameter представляет собой объект рефлексии параметров
        foreach ($reflector->getParameters() as $parameter) {
            // Потребление квоты параметра
            if ($parameter->hasType()) {
                $name = $parameter->getType()->getName();
                switch ($name) {
                    case 'int':
                    case 'string':
                    case 'bool':
                    case 'array':
                    case 'object':
                    case 'float':
                    case 'mixed':
                    case 'resource':
                        goto _else;
                    default:
                        if (is_a($request, $name)) {
                            // Внедрение Request
                            $parameters[] = $request;
                        } else {
                            $parameters[] = static::container($plugin)->make($name);
                        }
                        break;
                }
            } else {
                _else:
                // Переменный параметр
                if (null !== key($args)) {
                    $parameters[] = current($args);
                } else {
                    // Указывает, имеет ли текущий параметр значение по умолчанию. Если да, возвращает true
                    $parameters[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
                }
                // Потребление квоты переменных
                next($args);
            }
        }

        // Возвращает результат замены параметров
        return $parameters;
    }

    /**
     * @param string $plugin
     * @param array|Closure $call
     * @return Closure
     * @see Dependency injection through reflection information
     */
    protected static function resolveInject(string $plugin, array|Closure $call): Closure
    {
        return function (Request $request, ...$args) use ($plugin, $call) {
            $reflector = static::getReflector($call);
            $args = static::resolveMethodDependencies($plugin, $request, $args, $reflector);
            return $call(...$args);
        };
    }

    /**
     * Функция для создания ответа на исключение.
     *
     * @param Throwable $e Исключение.
     * @param mixed $request Запрос.
     * @return Response Возвращает ответ.
     */
    protected static function exceptionResponse(Throwable $e, mixed $request): Response
    {
        try {
            // Получаем приложение и плагин из запроса
            $app = $request->app ?: '';
            $plugin = $request->plugin ?: '';
            // Получаем конфигурацию исключений
            $exceptionConfig = static::config($plugin, 'exception');
            // Получаем класс обработчика исключений по умолчанию
            $defaultException = $exceptionConfig[''] ?? ExceptionHandler::class;
            // Получаем класс обработчика исключений для приложения
            $exceptionHandlerClass = $exceptionConfig[$app] ?? $defaultException;

            // Создаем экземпляр обработчика исключений
            /** @var ExceptionHandlerInterface $exceptionHandler */
            $exceptionHandler = static::container($plugin)->make($exceptionHandlerClass, [
                'logger' => static::$logger,
                'debug' => static::config($plugin, 'app.debug')
            ]);
            // Отправляем отчет об исключении
            $exceptionHandler->report($e);
            // Создаем ответ на исключение
            $response = $exceptionHandler->render($request, $e);
            $response->exception($e);
            return $response;
        } catch (Throwable $e) {
            // Если возникло исключение при обработке исключения, создаем ответ с кодом 500
            $response = new Response(500, [], static::config($plugin ?? '', 'app.debug') ? (string)$e : $e->getMessage());
            $response->exception($e);
            return $response;
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    protected static function stringify(mixed $data): string
    {
        $type = gettype($data);
        switch ($type) {
            case 'boolean':
                return $data ? 'true' : 'false';
            case 'NULL':
                return 'NULL';
            case 'array':
                return 'Array';
            case 'object':
                if (!method_exists($data, '__toString')) {
                    return 'Object';
                }
        }
        return (string)$data;

    }

    /**
     * Функция для поиска маршрута.
     *
     * @param TcpConnection $connection Соединение TCP.
     * @param string $path Путь.
     * @param string $key Ключ.
     * @param mixed $request Запрос.
     * @return bool Возвращает true, если маршрут найден, иначе возвращает false.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Throwable
     */
    protected static function findRoute(TcpConnection $connection, string $path, string $key, mixed $request): bool
    {
        // Получаем информацию о маршруте
        $routeInfo = Router::dispatch($request->method(), $path);
        if ($routeInfo[0] === Dispatcher::NOT_FOUND) {
            $routeInfo = Router::dispatch($request->method(), $request->host(true) . $path);
        }
        // Если маршрут найден
        if ($routeInfo[0] === Dispatcher::FOUND) {
            $routeInfo[0] = 'route';
            $callback = $routeInfo[1]['callback'];
            $route = clone $routeInfo[1]['route'];
            $app = $controller = $action = '';
            $args = !empty($routeInfo[2]) ? $routeInfo[2] : null;
            // Если есть аргументы, устанавливаем их для маршрута
            if ($args) {
                $route->setParams($args);
            }
            // Если обратный вызов - это массив
            if (is_array($callback)) {
                $controller = $callback[0];
                $plugin = static::getPluginByClass($controller);
                $app = static::getAppByController($controller);
                $action = static::getRealMethod($controller, $callback[1]) ?? '';
            } else {
                // Иначе получаем плагин по пути
                $plugin = static::getPluginByPath($path);
            }
            // Получаем обратный вызов
            $callback = static::getCallback($plugin, $app, $callback, $args, true, $route);
            // Собираем обратные вызовы
            static::collectCallbacks($key, [$callback, $plugin, $app, $controller ?: '', $action, $route]);
            // Получаем обратные вызовы
            [$callback, $request->plugin, $request->app, $request->controller, $request->action, $request->route] = static::$callbacks[$key];
            // Отправляем обратный вызов
            static::send($connection, $callback($request), $request);
            return true;
        }
        // Если маршрут не найден, возвращаем false
        return false;
    }

    /**
     * @param string $controllerClass
     * @return string
     */
    public static function getPluginByClass(string $controllerClass): string
    {
        $controllerClass = trim($controllerClass, '\\');
        $tmp = explode('\\', $controllerClass, 3);
        if ($tmp[0] !== 'plugin') {
            return '';
        }
        return $tmp[1] ?? '';
    }

    /**
     * @param string $controllerClass
     * @return mixed|string
     */
    protected static function getAppByController(string $controllerClass): mixed
    {
        $controllerClass = trim($controllerClass, '\\');
        $tmp = explode('\\', $controllerClass, 5);
        $pos = $tmp[0] === 'plugin' ? 3 : 1;
        if (!isset($tmp[$pos])) {
            return '';
        }
        return strtolower($tmp[$pos]) === 'controller' ? '' : $tmp[$pos];
    }

    /**
     * Получить метод
     * @param string $class
     * @param string $method
     * @return string
     */
    protected static function getRealMethod(string $class, string $method): string
    {
        $method = strtolower($method);
        $methods = get_class_methods($class);
        foreach ($methods as $candidate) {
            if (strtolower($candidate) === $method) {
                return $candidate;
            }
        }
        return $method;
    }

    /**
     * Функция для получения плагина по пути.
     *
     * @param string $path Путь.
     * @return string Возвращает имя плагина, если он найден, иначе возвращает пустую строку.
     */
    public static function getPluginByPath(string $path): string
    {
        // Удаляем слэши с начала и конца пути
        $path = trim($path, '/');
        // Разбиваем путь на части
        $tmp = explode('/', $path, 3);
        // Если первая часть пути не равна 'app', возвращаем пустую строку
        if ($tmp[0] !== 'app') {
            return '';
        }
        // Возвращаем вторую часть пути (имя плагина) или пустую строку, если она не существует
        return $tmp[1] ?? '';
    }

    /**
     * Функция для разбора контроллера и действия из пути.
     *
     * @param string $path Путь.
     * @return array|false Возвращает массив с информацией о контроллере и действии, если они найдены, иначе возвращает false.
     * @throws ReflectionException
     */
    protected static function parseControllerAction(string $path): false|array
    {
        // Удаляем дефисы из пути
        $path = str_replace(['-', '//'], ['', '/'], $path);

        static $cache = [];
        if (isset($cache[$path])) {
            return $cache[$path];
        }

        // Разбиваем путь на части
        $pathExplode = explode('/', trim($path, '/'));

        // Проверяем, является ли путь плагином
        $isPlugin = isset($pathExplode[1]) && $pathExplode[0] === 'app';

        // Получаем префиксы для конфигурации, пути и класса
        $configPrefix = $isPlugin ? "plugin.$pathExplode[1]." : '';
        $pathPrefix = $isPlugin ? "/app/$pathExplode[1]" : '';
        $classPrefix = $isPlugin ? "plugin\\$pathExplode[1]" : '';

        // Получаем суффикс контроллера из конфигурации
        $suffix = Config::get("{$configPrefix}app.controller_suffix", '');

        // Получаем относительный путь
        $relativePath = trim(substr($path, strlen($pathPrefix)), '/');
        $pathExplode = $relativePath ? explode('/', $relativePath) : [];

        // По умолчанию действие - это 'index'
        $action = 'index';

        // Пытаемся угадать контроллер и действие
        if (!$controllerAction = static::guessControllerAction($pathExplode, $action, $suffix, $classPrefix)) {
            // Если контроллер и действие не найдены и путь состоит из одной части, возвращаем false
            if (count($pathExplode) <= 1) {
                return false;
            }

            $action = end($pathExplode);
            unset($pathExplode[count($pathExplode) - 1]);
            $controllerAction = static::guessControllerAction($pathExplode, $action, $suffix, $classPrefix);
        }

        if ($controllerAction && !isset($path[256])) {
            $cache[$path] = $controllerAction;
            if (count($cache) > 1024) {
                unset($cache[key($cache)]);
            }
        }

        return $controllerAction;
    }


    /**
     * Функция для предположения контроллера и действия.
     *
     * @param array $pathExplode Массив с разделенными частями пути.
     * @param string $action Название действия.
     * @param string $suffix Суффикс.
     * @param string $classPrefix Префикс класса.
     * @return array|false Возвращает массив с информацией о контроллере и действии, если они найдены, иначе возвращает false.
     * @throws ReflectionException
     */
    protected static function guessControllerAction(array $pathExplode, string $action, string $suffix, string $classPrefix): false|array
    {
        // Создаем карту возможных путей к контроллеру
        $map[] = trim("$classPrefix\\app\\controller\\" . implode('\\', $pathExplode), '\\');
        foreach ($pathExplode as $index => $section) {
            $tmp = $pathExplode;
            array_splice($tmp, $index, 1, [$section, 'controller']);
            $map[] = trim("$classPrefix\\" . implode('\\', array_merge(['app'], $tmp)), '\\');
        }
        foreach ($map as $item) {
            $map[] = $item . '\\index';
        }

        // Проверяем каждый возможный путь
        foreach ($map as $controllerClass) {
            // Удаляем xx\xx\controller
            if (str_ends_with($controllerClass, '\\controller')) {
                continue;
            }
            $controllerClass .= $suffix;
            // Если контроллер и действие найдены, возвращаем информацию о них
            if ($controllerAction = static::getControllerAction($controllerClass, $action)) {
                return $controllerAction;
            }
        }

        // Если контроллер или действие не найдены, возвращаем false
        return false;
    }


    /**
     * Функция для получения контроллера и действия.
     *
     * @param string $controllerClass Имя класса контроллера.
     * @param string $action Название действия.
     * @return array|false Возвращает массив с информацией о контроллере и действии, если они найдены, иначе возвращает false.
     * @throws ReflectionException
     */
    protected static function getControllerAction(string $controllerClass, string $action): false|array
    {
        // Отключаем вызов магических методов
        if (str_starts_with($action, '__')) {
            return false;
        }

        // Если класс контроллера и действие найдены, возвращаем информацию о них
        if (($controllerClass = static::getController($controllerClass)) && ($action = static::getAction($controllerClass, $action))) {
            return [
                'plugin' => static::getPluginByClass($controllerClass),
                'app' => static::getAppByController($controllerClass),
                'controller' => $controllerClass,
                'action' => $action
            ];
        }

        // Если класс контроллера или действие не найдены, возвращаем false
        return false;
    }

    /**
     * Функция для получения контроллера.
     *
     * @param string $controllerClass Имя класса контроллера.
     * @return string|false Возвращает имя класса контроллера, если он найден, иначе возвращает false.
     * @throws ReflectionException
     */
    protected static function getController(string $controllerClass): false|string
    {
        // Если класс контроллера существует, возвращаем его имя
        if (class_exists($controllerClass)) {
            return (new ReflectionClass($controllerClass))->name;
        }

        // Разбиваем полное имя класса на части
        $explodes = explode('\\', strtolower(ltrim($controllerClass, '\\')));
        $basePath = $explodes[0] === 'plugin' ? Path::basePath('plugin') : app_path();
        unset($explodes[0]);
        $fileName = array_pop($explodes) . '.php';
        $found = true;

        // Ищем соответствующую директорию
        foreach ($explodes as $pathSection) {
            if (!$found) {
                break;
            }
            $dirs = scan_dir($basePath, false);
            $found = false;
            foreach ($dirs as $name) {
                $path = "$basePath/$name";
                if (is_dir($path) && strtolower($name) === $pathSection) {
                    $basePath = $path;
                    $found = true;
                    break;
                }
            }
        }

        // Если директория не найдена, возвращаем false
        if (!$found) {
            return false;
        }

        // Ищем файл контроллера в директории
        foreach (scandir($basePath) ?: [] as $name) {
            if (strtolower($name) === $fileName) {
                require_once "$basePath/$name";
                if (class_exists($controllerClass, false)) {
                    return (new ReflectionClass($controllerClass))->name;
                }
            }
        }

        // Если файл контроллера не найден, возвращаем false
        return false;
    }

    /**
     * Функция для получения действия контроллера.
     *
     * @param string $controllerClass Имя класса контроллера.
     * @param string $action Название действия.
     * @return string|false Возвращает название действия, если оно найдено, иначе возвращает false.
     */
    protected static function getAction(string $controllerClass, string $action): false|string
    {
        // Получаем все методы класса контроллера
        $methods = get_class_methods($controllerClass);
        $lowerAction = strtolower($action);
        $found = false;

        // Проверяем, есть ли метод, соответствующий действию
        foreach ($methods as $candidate) {
            if (strtolower($candidate) === $lowerAction) {
                $action = $candidate;
                $found = true;
                break;
            }
        }

        // Если действие найдено, возвращаем его
        if ($found) {
            return $action;
        }

        // Если действие не является публичным методом, возвращаем false
        if (method_exists($controllerClass, $action)) {
            return false;
        }

        // Если в классе контроллера есть метод __call, возвращаем действие
        if (method_exists($controllerClass, '__call')) {
            return $action;
        }

        // В противном случае возвращаем false
        return false;
    }

    /**
     * @param $server
     * @return void
     * @throws ErrorException
     */
    public function onServerStart($server): void
    {
        static::$server = $server;
        Http::requestClass(static::$requestClass);

        Environment::loadAll();
        Config::clear();
        Config::loadAll(['route']);

        set_error_handler(fn($level, $message, $file = '', $line = 0) => (error_reporting() & $level) ? throw new ErrorException($message, 0, $level, $file, $line) : true);
        register_shutdown_function(fn($start_time) => (time() - $start_time <= 1) ? sleep(1) : true, time());
        date_default_timezone_set(config('app.default_timezone', 'Europe/Moscow'));

        FileLoader::loadAll();
        EventLoader::loadAll();
        MiddlewareLoader::loadAll();
        BootstrapLoader::loadAll($server);

        $paths = [config_path()];
        foreach (scan_dir(Path::basePath('plugin')) as $path) {
            if (is_dir($path = "$path/config")) {
                $paths[] = $path;
            }
        }
        Router::load($paths);
    }
}
