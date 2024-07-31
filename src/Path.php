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

namespace Triangle\Engine;

class Path
{
    /**
     * @var string|null
     */
    public static ?string $basePath = null;

    /**
     * @var string|null
     */
    public static ?string $appPath = null;

    /**
     * @var string|null
     */
    public static ?string $configPath = null;

    /**
     * @var string|null
     */
    public static ?string $publicPath = null;

    /**
     * @var string|null
     */
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
        string $appPath = null,
        string $configPath = null,
        string $publicPath = null,
        string $runtimePath = null,
    )
    {
        static::$basePath = $basePath;
        static::$appPath = $appPath;
        static::$configPath = $configPath;
        static::$publicPath = $publicPath;
        static::$runtimePath = $runtimePath;
    }

    /**
     * @param false|string $path
     * @return string|null
     */
    public static function basePath(false|string $path = ''): ?string
    {
        if (false === $path) {
            return run_path();
        }
        return path_combine(static::$basePath ?? BASE_PATH, $path);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public static function appPath(string $path = ''): ?string
    {
        return path_combine(static::$appPath ?? config('app.app_path', static::basePath('app')), $path);
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

    /**
     * @param string $path
     * @return string|null
     */
    public static function configPath(string $path = ''): ?string
    {
        return path_combine(static::$configPath ?? static::basePath('config'), $path);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public static function publicPath(string $path = ''): ?string
    {
        return path_combine(static::$publicPath ?? config('app.public_path', run_path('public')), $path);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public static function runtimePath(string $path = ''): ?string
    {
        return path_combine(static::$runtimePath ?? config('app.runtime_path', run_path('runtime')), $path);
    }
}