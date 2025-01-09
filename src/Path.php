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

namespace Triangle\Engine;

use Phar;

class Path
{
    public static ?string $runPath = null;
    public static ?string $basePath = null;

    public static ?string $appPath = null;

    public static ?string $configPath = null;

    public static ?string $publicPath = null;

    public static ?string $runtimePath = null;

    /**
     * @param string|null $basePath
     * @param string|null $appPath
     * @param string|null $configPath
     * @param string|null $publicPath
     * @param string|null $runtimePath
     */
    public function __construct(
        string $basePath = null,
        string $configPath = null,
        string $appPath = null,
        string $publicPath = null,
        string $runtimePath = null,
    )
    {
        static::$basePath = $basePath ?? Path::basePath();
        static::$configPath = $configPath ?? Path::configPath();
        static::$appPath = $appPath ?? Path::appPath();
        static::$publicPath = $publicPath ?? Path::publicPath();
        static::$runtimePath = $runtimePath ?? Path::runtimePath();
    }

    public static function runPath(string $path = ''): string {
        static::$runPath ??= is_phar() ? dirname(Phar::running(false)) : static::basePath();
        return path_combine(static::$runPath, $path);

    }

    public static function basePath(false|string $path = ''): ?string
    {
        if (false === $path) {
            return static::runPath();
        }

        return path_combine(static::$basePath ?? BASE_PATH, $path);
    }

    public static function appPath(string $path = ''): ?string
    {
        static::$appPath ??= static::basePath('app');
        return path_combine(static::$appPath, $path);
    }

    public static function controllerPath(string $path = ''): ?string
    {
        return path_combine(static::appPath('controller'), $path);
    }

    public static function modelPath(string $path = ''): ?string
    {
        return path_combine(static::appPath('model'), $path);
    }

    public static function viewPath(string $path = ''): ?string
    {
        return path_combine(static::appPath('view'), $path);
    }

    public static function configPath(string $path = ''): ?string
    {
        static::$configPath ??= static::basePath('config');
        return path_combine(static::$configPath, $path);
    }

    public static function publicPath(string $path = ''): ?string
    {
        static::$publicPath ??= static::runPath('public');
        return empty($path) ? static::$publicPath : path_combine(static::$publicPath, $path);
    }

    public static function runtimePath(string $path = ''): ?string
    {
        static::$runtimePath ??= static::runPath('runtime');
        return path_combine(static::$runtimePath, $path);
    }
}