<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
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

namespace support\view;

use Jenssegers\Blade\Blade as BladeView;
use Triangle\Engine\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function is_array;
use function request;
use function runtime_path;

/**
 * rameX Blade: Templating adapter (jenssegers/blade)
 */
class Blade implements View
{
    /**
     * @var array
     */
    protected static array $vars = [];

    /**
     * @param array|string $name
     * @param mixed|null $value
     */
    public static function assign(array|string $name, mixed $value = null, $merge_recursive = false): void
    {
        if ($merge_recursive) {
            array_merge_recursive(static::$vars, is_array($name) ? $name : [$name => $value]);
        } else {
            static::$vars = array_merge(static::$vars, is_array($name) ? $name : [$name => $value]);
        }
    }

    public static function vars(): array
    {
        return static::$vars;
    }

    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @param string|null $plugin
     * @return string
     */
    public static function render(string $template, array $vars, string $app = null, string $plugin = null): string
    {
        static $views = [];
        $request = request();
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
        $app = $app === null ? $request->app : $app;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $key = "$plugin-$app";
        if (!isset($views[$key])) {
            $viewPath = $app === '' ? "$baseViewPath/view" : "$baseViewPath/$app/view";
            $views[$key] = new BladeView($viewPath, runtime_path() . '/views');
            $extension = config("{$configPrefix}view.extension");
            if ($extension) {
                $extension($views[$key]);
            }
        }
        $vars = array_merge(static::$vars, $vars);
        $content = $views[$key]->render($template, $vars);
        static::$vars = [];
        return $content;
    }
}
