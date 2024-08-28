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

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function array_replace_recursive;
use function array_reverse;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function key;
use function str_replace;

class Config
{
    /**
     * @var array
     */
    protected static array $config = [];

    /**
     * @var string
     */
    protected static string $configPath = '';

    /**
     * @var bool
     */
    protected static bool $loaded = false;

    public static function loadAll(array $excludes = []): void
    {
        Config::load(config_path(), $excludes);

        $directory = Path::basePath(config('app.plugin_alias', 'plugin'));
        foreach (scan_dir($directory, false) as $name) {
            $dir = "$directory/$name/config";
            if (is_dir($dir)) {
                Config::load($dir, $excludes, config('app.plugin_alias', 'plugin') . ".$name");
            }
        }
    }

    public static function reloadAll(array $excludes = []): void
    {
        static::clear();
        static::loadAll($excludes);
    }

    /**
     * Загрузи
     * @param string $configPath
     * @param array $excludeFile
     * @param string|null $key
     * @return void
     */
    public static function load(string $configPath, array $excludeFile = [], string $key = null): void
    {
        if (!$configPath) {
            return;
        }
        static::$configPath = $configPath;
        static::$loaded = false;
        $config = static::loadFromDir($configPath, $excludeFile);
        if (!$config) {
            static::$loaded = true;
            return;
        }
        if ($key !== null) {
            foreach (array_reverse(explode('.', $key)) as $k) {
                $config = [$k => $config];
            }
        }
        static::$config = array_replace_recursive(static::$config, $config);
        static::formatConfig();
        static::$loaded = true;
    }

    /**
     * Загрузить из папки
     * @param string $configPath
     * @param array $excludeFile
     * @return array
     */
    public static function loadFromDir(string $configPath, array $excludeFile = []): array
    {
        $allConfig = [];
        if (!is_dir($configPath)) {
            return [];
        }
        $dirIterator = new RecursiveDirectoryIterator($configPath, FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if (is_dir((string)$file) || $file->getExtension() != 'php' || in_array($file->getBaseName('.php'), $excludeFile)) {
                continue;
            }
            $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', substr((string)$file, 0, -4));
            $explode = array_reverse(explode(DIRECTORY_SEPARATOR, $relativePath));
            if (count($explode) >= 2) {
                $appConfigFile = $file->getPath() . '/app.php';
                if (!is_file($appConfigFile)) {
                    continue;
                }
                $appConfig = include $appConfigFile;
                if (empty($appConfig['enable'])) {
                    continue;
                }
            }
            $config = include $file;
            foreach ($explode as $section) {
                $tmp = [];
                $tmp[$section] = $config;
                $config = $tmp;
            }
            $allConfig = array_replace_recursive($allConfig, $config);
        }
        return $allConfig;
    }

    /**
     * Форматировать
     * @return void
     */
    protected static function formatConfig(): void
    {
        $config = static::$config;
        $plugin_path = $config['app']['plugin_alias'] ?? 'plugin';

        foreach ($config[$plugin_path] ?? [] as $plugin => $plugin_config) {
            if (!is_array($plugin_config)) {
                continue;
            }
            foreach ($plugin_config['log'] ?? [] as $key => $value) {
                $config['log']["$plugin_path.$plugin.$key"] = $value;
            }
            foreach ($plugin_config['database']['connections'] ?? [] as $key => $value) {
                $config['database']['connections']["$plugin_path.$plugin.$key"] = $value;
            }
            foreach ($plugin_config['redis'] ?? [] as $key => $value) {
                $config['redis']["$plugin_path.$plugin.$key"] = $value;
            }
        }

        foreach (config('plugin', []) as $vendor => $plugins) {
            foreach ($plugins as $plugin => $plugin_config) {
                if (!is_array($plugin_config)) {
                    continue;
                }
                foreach ($plugin_config['log'] ?? [] as $key => $value) {
                    $config['log']["plugin.$vendor.$plugin.$key"] = $value;
                }
                foreach ($plugin_config['database']['connections'] ?? [] as $key => $value) {
                    $config['database']['connections']["plugin.$vendor.$plugin.$key"] = $value;
                }
                foreach ($plugin_config['redis'] ?? [] as $key => $value) {
                    $config['redis']["plugin.$vendor.$plugin.$key"] = $value;
                }
            }
        }

        if (!empty($config['database']['connections'])) {
            $config['database']['default'] = $config['database']['default'] ?? key($config['database']['connections']);
        }

        static::$config = $config;
    }

    /**
     * Очистить
     * @return void
     */
    public static function clear(): void
    {
        static::$config = [];
    }

    /**
     * Получить
     * @param string|null $key
     * @param mixed|null $default
     * @return array|mixed|void|null
     */
    public static function get(string $key = null, mixed $default = null)
    {
        if ($key === null) {
            return static::$config;
        }
        $keyArray = explode('.', $key);
        $value = static::$config;
        $found = true;
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                if (static::$loaded) {
                    return $default;
                }
                $found = false;
                break;
            }
            $value = $value[$index];
        }
        if ($found) {
            return $value;
        }
        return static::read($key, $default);
    }

    /**
     * Считать
     * @param string $key
     * @param mixed|null $default
     * @return array|mixed|null
     */
    protected static function read(string $key, mixed $default = null): mixed
    {
        $path = static::$configPath;
        if ($path === '') {
            return $default;
        }
        $keys = $keyArray = explode('.', $key);
        foreach ($keyArray as $index => $section) {
            unset($keys[$index]);
            if (is_file($file = "$path/$section.php")) {
                $config = include $file;
                return static::find($keys, $config, $default);
            }
            if (!is_dir($path = "$path/$section")) {
                return $default;
            }
        }
        return $default;
    }

    /**
     * Найти
     * @param array $keyArray
     * @param mixed $stack
     * @param mixed $default
     * @return array|mixed
     */
    protected static function find(array $keyArray, mixed $stack, mixed $default): mixed
    {
        if (!is_array($stack)) {
            return $default;
        }
        $value = $stack;
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }

    /**
     * @param array $config
     */
    public static function set(array $config): void
    {
        static::$config = array_replace_recursive(static::$config, $config);
    }
}
