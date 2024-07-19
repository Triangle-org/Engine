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

namespace Triangle\Engine\View;

use think\Template;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function ob_get_clean;
use function ob_start;
use function runtime_path;

/**
 * Класс ThinkPHP
 * Этот класс представляет собой адаптер шаблонизатора (topthink/think-template) и наследует от абстрактного класса AbstractView.
 * Он также реализует интерфейс ViewInterface.
 */
class ThinkPHP extends AbstractView implements ViewInterface
{
    /**
     * Рендеринг представления.
     * @param string $template Шаблон для рендеринга
     * @param array $vars Переменные, которые должны быть доступны в шаблоне
     * @param string|null $app Приложение, которому принадлежит шаблон (необязательно)
     * @param string|null $plugin Плагин, которому принадлежит шаблон (необязательно)
     * @return string Результат рендеринга
     */
    public static function render(string $template, array $vars, string $app = null, string $plugin = null): string
    {
        $request = request();

        $app = $app === null ? ($request->app ?? '') : $app;
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;

        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $baseViewPath = $plugin ? base_path("plugin/$plugin/app") : app_path();
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');

        $viewPath = $app === '' ? "$baseViewPath/view/" : "$baseViewPath/$app/view/";
        $defaultOptions = [
            'view_path' => $viewPath,
            'cache_path' => runtime_path('views/'),
            'view_suffix' => $viewSuffix
        ];

        $options = array_merge($defaultOptions, config("{$configPrefix}view.options", []));
        $views = new Template($options);

        ob_start();

        if (isset($request->_view_vars)) {
            $vars = array_merge((array)$request->_view_vars, $vars);
        }
        $views->fetch($template, $vars);

        return ob_get_clean();
    }
}
