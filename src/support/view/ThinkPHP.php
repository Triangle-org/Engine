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

use think\Template;
use Triangle\Engine\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function is_array;
use function ob_get_clean;
use function ob_start;
use function request;
use function runtime_path;

/**
 * FrameX ThinkPHP: Templating adapter (topthink/think-template)
 */
class ThinkPHP implements View
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
        $request = request();
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
        $app = $app === null ? $request->app : $app;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $viewPath = $app === '' ? "$baseViewPath/view/" : "$baseViewPath/$app/view/";
        $defaultOptions = [
            'view_path' => $viewPath,
            'cache_path' => runtime_path() . '/views/',
            'view_suffix' => $viewSuffix
        ];
        $options = $defaultOptions + config("{$configPrefix}view.options", []);
        $views = new Template($options);
        ob_start();
        $vars = array_merge(static::$vars, $vars);
        $views->fetch($template, $vars);
        $content = ob_get_clean();
        static::$vars = [];
        return $content;
    }
}
