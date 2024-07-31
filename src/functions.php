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

use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use support\Response;
use Triangle\Engine\App;
use Triangle\Engine\Config;
use Triangle\Engine\Environment;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Path;

/** FORMATS HELPERS */

if (!function_exists('json')) {
    /**
     * @param $value
     * @param int $flags
     * @return string|false
     */
    function json($value, int $flags = JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR): false|string
    {
        return json_encode($value, $flags);
    }
}

if (!function_exists('jsonp')) {
    /**
     * @param $data
     * @param string $callbackName
     * @return Response
     */
    function jsonp($data, string $callbackName = 'callback'): Response
    {
        if (!is_scalar($data) && null !== $data) {
            $data = json_encode($data);
        }
        return new Response(200, [], "$callbackName($data)");
    }
}

/** SYSTEM HELPERS */

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * * @param mixed|null $default
     * @return mixed
     */
    function config(string $key = null, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    function env(string $key = null, mixed $default = null): mixed
    {
        return Environment::get($key, $default);
    }
}

if (!function_exists('setEnv')) {
    /**
     * @param array $values
     * @return bool
     */
    function setEnv(array $values): bool
    {
        return Environment::set($values, config('env_file', '.env'));
    }
}

/** PATHS HELPERS */

/**
 * return the program execute directory
 * @param string $path
 * @return string
 */
function run_path(string $path = ''): string
{
    static $runPath = '';
    if (!$runPath) {
        $runPath = is_phar() ?
            dirname(Phar::running(false)) :
            base_path();
    }
    return path_combine($runPath, $path);
}

/**
 * @param false|string $path
 * @return string
 */
function base_path(false|string $path = ''): string
{
    return Path::basePath($path);
}

/**
 * @param string $path
 * @return string
 */
function app_path(string $path = ''): string
{
    return Path::appPath($path);
}

/**
 * @param string $path
 * @return string
 */
function config_path(string $path = ''): string
{
    return Path::configPath($path);
}

/**
 * @param string $path
 * @return string
 */
function public_path(string $path = ''): string
{
    return Path::publicPath($path);
}

/**
 * @param string $path
 * @return string
 */
function runtime_path(string $path = ''): string
{
    return Path::runtimePath($path);
}

/**
 * @param string $path
 * @return string
 */
function view_path(string $path = ''): string
{
    return path_combine(app_path('view'), $path);
}

/**
 * Generate paths based on given information
 * @param string $front
 * @param string $back
 * @return string
 */
function path_combine(string $front, string $back): string
{
    return $front . ($back ? (DIRECTORY_SEPARATOR . ltrim($back, DIRECTORY_SEPARATOR)) : $back);
}

/**
 * Get realpath
 * @param string $filePath
 * @return string|false
 */
function get_realpath(string $filePath): string|false
{
    if (str_starts_with($filePath, 'phar://')) {
        return $filePath;
    } else {
        return realpath($filePath);
    }
}

/** DIR HELPERS */

/**
 * Copy dir
 * @param string $source
 * @param string $dest
 * @param bool $overwrite
 * @return void
 */
function copy_dir(string $source, string $dest, bool $overwrite = false): void
{
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            create_dir($dest);
        }
        $files = array_diff(scandir($source), ['.', '..']) ?: [];
        foreach ($files as $file) {
            copy_dir("$source/$file", "$dest/$file", $overwrite);
        }
    } else if (file_exists($source) && ($overwrite || !file_exists($dest))) {
        copy($source, $dest);
    }
}

/**
 * ScanDir.
 * @param string $basePath
 * @param bool $withBasePath
 * @return array
 */
function scan_dir(string $basePath, bool $withBasePath = true): array
{
    if (!is_dir($basePath)) {
        return [];
    }
    $paths = array_diff(scandir($basePath), ['.', '..']) ?: [];
    return $withBasePath ? array_map(fn($path) => $basePath . DIRECTORY_SEPARATOR . $path, $paths) : $paths;
}

/**
 * Remove dir
 * @param string $dir
 * @return bool
 */
function remove_dir(string $dir): bool
{
    if (is_link($dir) || is_file($dir)) {
        return file_exists($dir) && unlink($dir);
    }
    $files = array_diff(scandir($dir), ['.', '..']) ?: [];
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) && !is_link($path) ? remove_dir($path) : file_exists($path) && unlink($path);
    }
    return file_exists($dir) && rmdir($dir);
}

/**
 * Create directory
 * @param string $dir
 * @return bool
 */
function create_dir(string $dir): bool
{
    return mkdir($dir, 0777, true);
}

/**
 * Rename directory
 * @param string $oldName
 * @param string $newName
 * @return bool
 */
function rename_dir(string $oldName, string $newName): bool
{
    return rename($oldName, $newName);
}

/** SERVER HELPERS */

/**
 * @param $processName
 * @param $config
 * @return void
 */
function server_start($processName, $config): void
{
    localzet_start(
        name: $processName,
        count: $config['count'] ?? cpu_count() * 4,
        listen: $config['listen'] ?? null,
        context: $config['context'] ?? [],
        user: $config['user'] ?? '',
        group: $config['group'] ?? '',
        reloadable: $config['reloadable'] ?? true,
        reusePort: $config['reusePort'] ?? false,
        protocol: $config['protocol'] ?? null,
        transport: $config['transport'] ?? 'tcp',
        handler: $config['handler'] ?? null,
        constructor: $config['constructor'] ?? [],
        onServerStart: function (?Server $server) use ($config) {
            if (file_exists(base_path('/support/bootstrap.php'))) {
                include_once base_path('/support/bootstrap.php');
            }
            if (isset($config['onServerStart']) && is_callable($config['onServerStart'])) {
                $config['onServerStart']($server);
            }
        },
        services: $config['services'] ?? [],
    );
}

/**
 * @return TcpConnection|null
 */
function connection(): ?TcpConnection
{
    return App::connection();
}

/**
 * @return \support\Request|Request|null
 */
function request(): \support\Request|Request|null
{
    return App::request();
}

/**
 * @return Server|null
 */
function server(): ?Server
{
    return App::server();
}

/**
 * @return bool
 */
function is_phar(): bool
{
    return class_exists(Phar::class, false) && Phar::running();
}

/**
 * Генерация ID
 *
 * @return string
 */
function generateId(): string
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}