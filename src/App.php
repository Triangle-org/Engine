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
use Illuminate\Database\Eloquent\Model;
use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use localzet\ServerAbstract;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;
use Triangle\Exception\ExceptionHandler;
use Triangle\Exception\ExceptionHandlerInterface;
use Triangle\Exception\InputTypeException;
use Triangle\Exception\InputValueException;
use Triangle\Exception\MissingInputException;

abstract class App extends ServerAbstract
{
    /**
     * @var array[]
     */
    protected static array $callbacks = [];

    protected static ?Server $server = null;

    protected static ?Logger $logger = null;


    /**
     * @var class-string|null
     */
    protected static ?string $requestClass = null;

    /**
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

        Path::$basePath = $basePath ?? Path::basePath();
        Path::$configPath = $configPath ?? Path::configPath();
        Path::$appPath = $appPath ?? Path::appPath();
        Path::$publicPath = $publicPath ?? Path::publicPath();
        Path::$runtimePath = $runtimePath ?? Path::runtimePath();
    }

    /**
     * @throws ErrorException
     */
    public function onServerStart(Server &$server): void
    {
        static::$server = $server;
        if (class_exists($server->protocol) && method_exists($server->protocol, 'requestClass')) {
            $server->protocol::requestClass(static::$requestClass);
        }
        
        Context::init();
        Autoload::loadAll($server);
    }

    public static function connection(): TcpConnection|null
    {
        return Context::get(TcpConnection::class);
    }

    public static function request(): Request|null
    {
        return Context::get(static::$requestClass);
    }

    public static function server(): ?Server
    {
        return static::$server;
    }

    /**
     * Получение рефлектора.
     *
     * @param mixed $call Вызов.
     * @return ReflectionFunction|ReflectionMethod Возвращает рефлектор функции или метода.
     * @throws ReflectionException
     */
    protected static function getReflector(mixed $call): ReflectionMethod|ReflectionFunction
    {
        if ($call instanceof Closure || is_string($call)) {
            return new ReflectionFunction($call);
        }
        
        return new ReflectionMethod($call[0], $call[1]);
    }

    protected static function stringify(mixed $data): string
    {
        $type = gettype($data);
        switch ($type) {
            case 'string':
                return $data;
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
     * Получить метод
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
     * @return mixed|string
     */
    protected static function getAppByController(string $controllerClass): mixed
    {
        $controllerClass = trim($controllerClass, '\\');
        $tmp = explode('\\', $controllerClass, 5);
        $pos = $tmp[0] === config('app.plugin_alias', 'plugin') ? 3 : 1;
        if (!isset($tmp[$pos])) {
            return '';
        }
        
        return strtolower($tmp[$pos]) === 'controller' ? '' : $tmp[$pos];
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

        // Проверяем, является ли путь плагином
        $plugin = Plugin::app_by_path($path);

        // Получаем суффикс контроллера из конфигурации
        $suffix = config('app.controller_suffix', '', $plugin);

        // Получаем префиксы для конфигурации, пути и класса
        $pathPrefix = $plugin ? "/" . config('app.plugin_uri', 'app') . "/$plugin" : '';
        $classPrefix = $plugin ? config('app.plugin_alias', 'plugin') . "\\$plugin" : '';

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
                'plugin' => Plugin::app_by_class($controllerClass),
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
        $basePath = $explodes[0] === config('app.plugin_alias', 'plugin') ? plugin_path() : app_path();
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
            foreach ($dirs as $dir) {
                $path = "$basePath/{$dir}";
                if (is_dir($path) && strtolower((string) $dir) === $pathSection) {
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
        foreach ($methods as $method) {
            if (strtolower($method) === $lowerAction) {
                $action = $method;
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
     * @throws Throwable
     */
    protected static function unsafeUri(string $path): bool
    {
        return !$path || $path[0] !== '/' || str_contains($path, '/../') || str_ends_with($path, '/..') || str_contains($path, "\\") || str_contains($path, "\0");
    }

    /**
     * Выполнить php файл
     * @return false|string
     */
    public static function execPhpFile(string $file): false|string
    {
        ob_start();
        try {
            include $file;
        } catch (Exception $exception) {
            echo $exception;
        }
        
        return ob_get_clean();
    }

    /**
     * Функция для получения зависимых параметров.
     *
     * @param Request $request Запрос.
     * @param ReflectionFunctionAbstract $reflectionFunctionAbstract Рефлектор.
     * @return array Возвращает массив с зависимыми параметрами.
     * @throws ReflectionException
     */
    protected static function resolveMethodDependencies(
        ContainerInterface         $container,
        Request                    $request,
        array                      $inputs,
        ReflectionFunctionAbstract $reflectionFunctionAbstract,
        bool                       $debug
    ): array
    {
        $parameters = [];
        foreach ($reflectionFunctionAbstract->getParameters() as $parameter) {
            $parameterName = $parameter->name;
            $type = $parameter->getType();
            $typeName = $type?->getName();

            if ($typeName && $request instanceof $typeName) {
                $parameters[$parameterName] = $request;
                continue;
            }

            if (!array_key_exists($parameterName, $inputs)) {
                if (!$parameter->isDefaultValueAvailable()) {
                    if (!$typeName || (!class_exists($typeName) && !enum_exists($typeName)) || enum_exists($typeName)) {
                        throw (new MissingInputException())->data([
                            'parameter' => $parameterName,
                        ])->debug($debug);
                    }
                } else {
                    $parameters[$parameterName] = $parameter->getDefaultValue();
                    continue;
                }
            }
            
            $parameterValue = $inputs[$parameterName] ?? null;
            switch ($typeName) {
                case 'int':
                case 'float':
                    if (!is_numeric($parameterValue)) {
                        throw (new InputTypeException())->data([
                            'parameter' => $parameterName,
                            'exceptType' => $typeName,
                            'actualType' => gettype($parameterValue),
                        ])->debug($debug);
                    }
                    
                    $parameters[$parameterName] = $typeName === 'float' ? (float)$parameterValue : (int)$parameterValue;
                    break;
                case 'bool':
                    $parameters[$parameterName] = (bool)$parameterValue;
                    break;
                case 'array':
                case 'object':
                    if (!is_array($parameterValue)) {
                        throw (new InputTypeException())->data([
                            'parameter' => $parameterName,
                            'exceptType' => $typeName,
                            'actualType' => gettype($parameterValue),
                        ])->debug($debug);
                    }
                    
                    $parameters[$parameterName] = $typeName === 'object' ? (object)$parameterValue : $parameterValue;
                    break;
                case 'string':
                case 'mixed':
                case 'resource':
                case null:
                    $parameters[$parameterName] = $parameterValue;
                    break;
                default:
                    $subInputs = is_array($parameterValue) ? $parameterValue : [];
                    if (class_exists(Model::class) && is_a($typeName, Model::class, true)) {
                        $parameters[$parameterName] = $container->make($typeName, [
                            'attributes' => $subInputs,
                            'data' => $subInputs
                        ]);
                        break;
                    }
                    
                    if (enum_exists($typeName)) {
                        $reflection = new ReflectionEnum($typeName);
                        if ($reflection->hasCase($parameterValue)) {
                            $parameters[$parameterName] = $reflection->getCase($parameterValue)->getValue();
                            break;
                        } elseif ($reflection->isBacked()) {
                            foreach ($reflection->getCases() as $case) {
                                if ($case->getValue()->value == $parameterValue) {
                                    $parameters[$parameterName] = $case->getValue();
                                    break;
                                }
                            }
                        }
                        
                        if (!array_key_exists($parameterName, $parameters)) {
                            throw (new InputValueException())->data([
                                'parameter' => $parameterName,
                                'enum' => $typeName
                            ])->debug($debug);
                        }
                        
                        break;
                    }
                    
                    if ($constructor = (new ReflectionClass($typeName))->getConstructor()) {
                        $parameters[$parameterName] = $container->make($typeName, static::resolveMethodDependencies($container, $request, $subInputs, $constructor, $debug));
                    } else {
                        $parameters[$parameterName] = $container->make($typeName);
                    }
                    
                    break;
            }
        }

        return $parameters;
    }

    /**
     * @see Dependency injection through reflection information
     */
    protected static function resolveInject(?string $plugin, array|Closure $call, ?array $args): Closure
    {
        return function (Request $request) use ($plugin, $call, $args) {
            $reflector = static::getReflector($call);
            $args = array_values(static::resolveMethodDependencies(
                config('container', plugin: $plugin),
                $request,
                array_merge($request->all(), $args),
                $reflector,
                config('app.debug', plugin: $plugin)
            ));
            return $call(...$args);
        };
    }

    protected static function collectCallbacks(string $key, array $data): void
    {
        static::$callbacks[$key] = $data;
        if (count(static::$callbacks) >= 1024) {
            unset(static::$callbacks[key(static::$callbacks)]);
        }
    }

    /**
     * @param $request
     * @return array|null
     */
    protected static function getCallbacks(string $key, &$request): Closure|callable|null
    {
        if (!isset(static::$callbacks[$key])) {
            return null;
        }

        [
            $callback,
            $request->plugin,
            $request->app,
            $request->controller,
            $request->action,
            $request->route
        ] = static::$callbacks[$key];
        return $callback;
    }

    /**
     * Проверка, требуется ли внедрение зависимостей.
     * @param $call
     * @throws ReflectionException
     */
    protected static function isNeedInject($call, array &$args): bool
    {
        if (is_array($call) && !method_exists($call[0], $call[1])) {
            return false;
        }

        $reflector = static::getReflector($call);
        $reflectionParameters = $reflector->getParameters();
        if (!$reflectionParameters) {
            return false;
        }
        
        $reflectionParameter = current($reflectionParameters);
        unset($reflectionParameters[key($reflectionParameters)]);
        $adaptersList = ['int', 'string', 'bool', 'array', 'object', 'float', 'mixed', 'resource'];
        $keys = [];
        $needInject = false;
        foreach ($reflectionParameters as $parameter) {
            $parameterName = $parameter->name;
            $keys[] = $parameterName;
            if ($parameter->hasType()) {
                $typeName = $parameter->getType()->getName();
                if (!in_array($typeName, $adaptersList)) {
                    $needInject = true;
                    continue;
                }
                
                if (!array_key_exists($parameterName, $args)) {
                    $needInject = true;
                    continue;
                }
                
                switch ($typeName) {
                    case 'int':
                    case 'float':
                        if (!is_numeric($args[$parameterName])) {
                            return true;
                        }
                        
                        $args[$parameterName] = $typeName === 'int' ? (int)$args[$parameterName] : (float)$args[$parameterName];
                        break;
                    case 'bool':
                        $args[$parameterName] = (bool)$args[$parameterName];
                        break;
                    case 'array':
                    case 'object':
                        if (!is_array($args[$parameterName])) {
                            return true;
                        }
                        
                        $args[$parameterName] = $typeName === 'array' ? $args[$parameterName] : (object)$args[$parameterName];
                        break;
                    case 'string':
                    case 'mixed':
                    case 'resource':
                        break;
                }
            }
        }
        
        if (array_keys($args) !== $keys) {
            return true;
        }
        
        if (!$reflectionParameter->hasType()) {
            return $reflectionParameter->getName() !== 'request';
        }

        if (!is_a(static::$requestClass, $reflectionParameter->getType()->getName(), true)) {
            return true;
        }

        return $needInject;
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
        // Получаем приложение и плагин из запроса
        $app = $request->app ?: '';
        $plugin = $request->plugin ?: '';

        try {
            // Получаем конфигурацию исключений
            $exceptionConfig = config('exception', plugin: $plugin);
            $appExceptionConfig = config('exception');

            // Получаем класс обработчика исключений по умолчанию
            if (!isset($exceptionConfig['']) && isset($appExceptionConfig['@'])) {
                $defaultException = $appExceptionConfig['@'] ?? ExceptionHandler::class;
            } else {
                $defaultException = $exceptionConfig[''] ?? ExceptionHandler::class;
            }

            // Получаем класс обработчика исключений для приложения
            $exceptionHandlerClass = $exceptionConfig[$app] ?? $defaultException;

            // Создаем экземпляр обработчика исключений
            /** @var ExceptionHandlerInterface $exceptionHandler */
            $exceptionHandler = (config('container', config('container'), $plugin))
                ->make($exceptionHandlerClass, ['logger' => static::$logger]);

            // Отправляем отчет об исключении
            $exceptionHandler->report($e);
            // Создаем ответ на исключение
            $response = $exceptionHandler->render($request, $e);
            $response->exception($e);
            return $response;
        } catch (Throwable $throwable) {
            // Если возникло исключение при обработке исключения, создаем ответ с кодом 500
            $response = new Response(500, [], config('app.debug', plugin: $plugin) ? (string)$throwable : $throwable->getMessage());
            $response->exception($throwable);
            return $response;
        }
    }
}