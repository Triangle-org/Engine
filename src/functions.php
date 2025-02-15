<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
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
use support\Translation;
use Triangle\Engine\Config;
use Triangle\Engine\Environment;
use Triangle\Engine\Path;
use Triangle\Request;
use Triangle\Response;

$install_path = Composer\InstalledVersions::getRootPackage()['install_path'] ?? null;
define('BASE_PATH', str_starts_with($install_path, 'phar://') ? $install_path : realpath($install_path) ?? dirname(__DIR__));


/** TRANSLATION HELPERS */

if (!function_exists('trans')) {
    /**
     * Translation
     * @param string|null $domain
     * @param string|null $locale
     */
    function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $res = Translation::trans($id, $parameters, $domain, $locale);
        return $res === '' ? $id : $res;
    }
}

if (!function_exists('locale')) {
    /**
     * Locale
     * @param string|null $locale
     */
    function locale(string $locale = null): string
    {
        if (!$locale) {
            return Translation::getLocale();
        }

        Translation::setLocale($locale);
        return $locale;
    }
}

/** FORMATS HELPERS */

if (!function_exists('json')) {
    /**
     * @param $value
     * @return string|false
     */
    function json($value, int $flags = JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR): false|string
    {
        return json_encode($value, $flags);
    }
}


/** SYSTEM HELPERS */

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * @param mixed|null $default
     */
    function config(string $key = null, mixed $default = null, ?string $plugin = null): mixed
    {
        return empty($plugin)
            ? Config::get($key, $default)
            : plugin($plugin . ($key ? ".$key" : ''), $default);
    }
}

if (!function_exists('plugin')) {
    /**
     * @param string|null $key
     * * @param mixed|null $default
     */
    function plugin(string $key = null, mixed $default = null): mixed
    {
        return Config::get(
            Config::get('app.plugin_alias', 'plugin') . ($key ? ".$key" : ''),
            $default);
    }
}

if (!function_exists('env')) {
    /**
     * @param string|null $key
     * @param mixed|null $default
     */
    function env(string $key = null, mixed $default = null): mixed
    {
        return Environment::get($key, $default);
    }
}

if (!function_exists('setEnv')) {
    function setEnv(array $values): bool
    {
        return Environment::set($values);
    }
}

/** PATHS HELPERS */
/**
 * return the program execute directory
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

function base_path(false|string $path = ''): string
{
    return Path::basePath($path);
}

function app_path(string $path = ''): string
{
    return Path::appPath($path);
}

function config_path(string $path = ''): string
{
    return Path::configPath($path);
}

function public_path(string $path = ''): string
{
    return Path::publicPath($path);
}

function runtime_path(string $path = ''): string
{
    return Path::runtimePath($path);
}

function view_path(string $path = ''): string
{
    return path_combine(app_path('view'), $path);
}

function plugin_path(string $path = ''): string
{
    return path_combine(Path::basePath(config('app.plugin_alias', 'plugin')), $path);
}

/**
 * Generate paths based on given information
 */
function path_combine(string $front, string $back): string
{
    return $front . ($back ? (DIRECTORY_SEPARATOR . ltrim($back, DIRECTORY_SEPARATOR)) : $back);
}

/**
 * Get realpath
 * @return string|false
 */
function get_realpath(string $filePath): string|false
{
    if (str_starts_with($filePath, 'phar://')) {
        return $filePath;
    }

    return realpath($filePath);
}

/** DIR HELPERS */
/**
 * Copy dir
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
    } elseif (file_exists($source) && ($overwrite || !file_exists($dest))) {
        copy($source, $dest);
    }
}

/**
 * ScanDir.
 */
function scan_dir(string $basePath, bool $withBasePath = true): array
{
    if (!is_dir($basePath)) {
        return [];
    }

    $paths = array_diff(scandir($basePath), ['.', '..']) ?: [];
    return $withBasePath ? array_map(fn($path): string => $basePath . DIRECTORY_SEPARATOR . $path, $paths) : $paths;
}

/**
 * Remove dir
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
 */
function create_dir(string $dir): bool
{
    return mkdir($dir, 0777, true);
}

/**
 * Rename directory
 */
function rename_dir(string $oldName, string $newName): bool
{
    return rename($oldName, $newName);
}

function is_phar(): bool
{
    return class_exists(Phar::class, false) && Phar::running();
}

/**
 * Генерация ID
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

if (!function_exists('connection')) {
    function connection(): ?TcpConnection
    {
        return config('server.handler')::connection();
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return config('server.handler')::request();
    }
}

if (!function_exists('server')) {
    function server(): ?Server
    {
        return config('server.handler')::server();
    }
}

if (!function_exists('response')) {
    /**
     * @throws Throwable
     */
    function response(mixed $data = '', int $status = 200, array $headers = [], bool $raw = false): Response
    {
        if ($raw) {
            $body = $data;
        } else {
            $body = ['status' => $status, 'data' => $data];

            if (config('app.debug')) {
                $body['debug'] = config('app.debug');
            }
        }
        $status = config('app.http_always_200') ? 200 : $status;

        if (!function_exists('responseView') || request()->expectsJson()) {
            return responseJson($body, $status, $headers);
        }

        return responseView($body, $status, $headers);
    }
}

function responseBlob(string $blob, string $type = 'text/plain'): Response
{

    return new Response(200, ['Content-Type' => $type], $blob);
}

/**
 * @param $data
 */
function responseJson($data, int $status = 200, array $headers = [], int $options = JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR): Response
{
    return new Response($status, ['Content-Type' => 'application/json'] + $headers, json($data, $options));
}

function redirect(string $location, int $status = 302, array $headers = []): Response
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }

    return $response;
}

if (!function_exists('not_found')) {
    /**
     * @throws Throwable
     */
    function not_found(): Response
    {
        return response('Ничего не найдено', 404);
    }
}

if (!function_exists('jsonp')) {
    /**
     * @param $data
     */
    function jsonp($data, string $callbackName = 'callback'): Response
    {
        if (!is_scalar($data) && null !== $data) {
            $data = json_encode($data);
        }

        return new Response(200, [], "$callbackName($data)");
    }
}