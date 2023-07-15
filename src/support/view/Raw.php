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

use Throwable;
use Triangle\Engine\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function extract;
use function is_array;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function request;

/**
 * FrameX Raw: PHP Templating engine
 */
class Raw implements View
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
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $view_global = config("{$configPrefix}view.options.view_global", false);
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $view_head = config("{$configPrefix}view.options.view_head", "base");
        $view_footer = config("{$configPrefix}view.options.view_footer", "footer");
        $app = $app === null ? $request->app : $app;
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $__template_body__ = $app === '' ? "$baseViewPath/view/$template.$viewSuffix" : "$baseViewPath/$app/view/$template.$viewSuffix";
        $__template_head__ = ($view_global ? app_path() : $baseViewPath) . ($app === '' || $view_global ? "/view/$view_head.$viewSuffix" : "/$app/view/$view_head.$viewSuffix");
        $__template_foot__ = ($view_global ? app_path() : $baseViewPath) . ($app === '' || $view_global ? "/view/$view_footer.$viewSuffix" : "/$app/view/$view_footer.$viewSuffix");

        $name = config('app.name', 'Triangle App');
        $description = config('app.description', 'Simple web application on Triangle Engine');
        $keywords = config('app.keywords', '');
        $viewport = config('app.viewport', 'width=device-width, initial-scale=1');

        $domain = config('app.domain', 'https://' . $request->host(true));
        $canonical = config('app.canonical', $request->url());
        $assets = config('app.assets', '/');
        $logo = config('app.logo', '/favicon.svg');

        $owner = config('app.owner', '');
        $designer = config('app.designer', '');
        $author = config('app.author', '');
        $copyright = config('app.copyright', '');
        $reply_to = config('app.reply_to', '');

        $page = last(explode('/', $template) ?? [$template]);

        $AppInfo = [
            'name' => $name,
            'description' => $description,
            'keywords' => $keywords,
            'viewport' => $viewport,

            'owner' => $owner,
            'designer' => $designer,
            'author' => $author,
            'copyright' => $copyright,
            'reply_to' => $reply_to,

            'domain' => $domain,
            'canonical' => $canonical,
            'assets' => $assets,
            'logo' => $logo,
        ];

        extract(static::$vars);
        extract($vars);
        ob_start();

        try {
            if (file_exists($__template_head__)) include $__template_head__;
            include $__template_body__;
            if (file_exists($__template_foot__)) include $__template_foot__;
        } catch (Throwable $e) {
            static::$vars = [];
            ob_end_clean();
            throw $e;
        }

        static::$vars = [];
        return ob_get_clean();
    }

    /**
     * @param string $template error/success
     * @param array $vars
     * @return false|string
     */
    public static function renderSys(string $template, array $vars): false|string
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        $config_prefix = $plugin ? "plugin.$plugin." : '';
        $sysview = __DIR__ . "/response/$template.phtml";
        $view = config("{$config_prefix}view.system.$template", $sysview);

        $name = config('app.name', 'Triangle App');
        $description = config('app.description', 'Simple web application on Triangle Engine');
        $keywords = config('app.keywords', '');
        $viewport = config('app.viewport', 'width=device-width, initial-scale=1');

        $domain = config('app.domain', 'https://' . $request->host(true));
        $canonical = config('app.canonical', $request->url());
        $assets = config('app.assets', '/');
        $logo = config('app.logo', '/favicon.svg');

        $owner = config('app.owner', '');
        $designer = config('app.designer', '');
        $author = config('app.author', '');
        $copyright = config('app.copyright', '');
        $reply_to = config('app.reply_to', '');

        $page = last(explode('/', $template) ?? [$template]);

        $AppInfo = [
            'name' => $name,
            'description' => $description,
            'keywords' => $keywords,
            'viewport' => $viewport,

            'owner' => $owner,
            'designer' => $designer,
            'author' => $author,
            'copyright' => $copyright,
            'reply_to' => $reply_to,

            'domain' => $domain,
            'canonical' => $canonical,
            'assets' => $assets,
            'logo' => $logo,
        ];

        extract(static::$vars);
        extract($vars);
        ob_start();

        try {
            include $view;
        } catch (Throwable $e) {
            static::$vars = [];
            ob_end_clean();
            throw $e;
        }

        static::$vars = [];
        return ob_get_clean();
    }
}
