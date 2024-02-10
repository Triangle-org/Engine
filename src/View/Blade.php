<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Zorin Projects S.P.
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
 *              For any questions, please contact <creator@localzet.com>
 */

namespace Triangle\Engine\View;

use Jenssegers\Blade\Blade as BladeView;
use Triangle\Engine\App;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function runtime_path;

/**
 * Класс Blade
 * Этот класс представляет собой адаптер шаблонизатора (jenssegers/blade) и наследует от абстрактного класса AbstractView.
 * Он также реализует интерфейс ViewInterface.
 */
class Blade extends AbstractView implements ViewInterface
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
        static $views = [];
        $request = request();

        $app = $app === null ? ($request->app ?? '') : $app;
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;

        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $baseViewPath = $plugin ? base_path("plugin/$plugin/app") : app_path();

        $key = "$plugin-$app";
        if (!isset($views[$key])) {

            $viewPath = $app === '' ?
                "$baseViewPath/view" :
                "$baseViewPath/$app/view";

            $views[$key] = new BladeView($viewPath, runtime_path('views'));

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
