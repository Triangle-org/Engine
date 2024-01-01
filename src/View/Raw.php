<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Localzet Group
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

namespace Triangle\Engine\View;

use Throwable;
use Triangle\Engine\App;
use function config;
use function extract;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;

/**
 * Класс Raw
 * Этот класс представляет собой движок шаблонизации PHP и наследует от абстрактного класса AbstractView.
 * Он также реализует интерфейс ViewInterface.
 */
class Raw extends AbstractView implements ViewInterface
{
    /**
     * Рендеринг представления.
     * @param string $template Шаблон для рендеринга
     * @param array $vars Переменные, которые должны быть доступны в шаблоне
     * @param string|null $app Приложение, которому принадлежит шаблон (необязательно)
     * @param string|null $plugin Плагин, которому принадлежит шаблон (необязательно)
     * @return string Результат рендеринга
     * @throws Throwable
     */
    public static function render(string $template, array $vars, string $app = null, string $plugin = null): string
    {
        $configPrefix = $plugin ? "plugin.$plugin." : '';

        foreach (config("{$configPrefix}view.options.pre_renders", []) as $render) {
            if (isset($render['template'])) {
                static::assign(@$render['vars'] ?? []);
                static::addPreRender(
                    $render['template'],
                    @$render['app'] ?? null,
                    @$render['plugin'] ?? null,
                );
            }
        }

        foreach (config("{$configPrefix}view.options.post_renders", []) as $render) {
            if (isset($render['template'])) {
                static::assign(@$render['vars'] ?? []);
                static::addPostRender(
                    $render['template'],
                    @$render['app'] ?? null,
                    @$render['plugin'] ?? null,
                );
            }
        }

        $preRenders = static::getPreRenders();
        $curRender = [
            'template' => $template,
            'app' => $app,
            'plugin' => $plugin
        ];
        $postRenders = static::getPostRenders();

        extract(config("{$configPrefix}view.options.vars", []));
        extract(static::$vars);
        extract($vars);
        ob_start();

        try {
            foreach ($preRenders as $render) {
                $file = static::build($render);
                if (file_exists($file)) include $file;
            }

            include static::build($curRender);

            foreach ($postRenders as $render) {
                $file = static::build($render);
                if (file_exists($file)) include $file;
            }
        } catch (Throwable $e) {
            static::$vars = [];
            ob_end_clean();
            throw $e;
        }

        static::$vars = [];
        return ob_get_clean();
    }

    /**
     * Рендеринг системного представления.
     * @param string $template Шаблон для рендеринга
     * @param array $vars Переменные, которые должны быть доступны в шаблоне
     * @return false|string Результат рендеринга
     */
    public static function renderSys(string $template, array $vars): false|string
    {
        $request = App::request();
        $plugin = $request->plugin ?? '';
        $configPrefix = $plugin ? "plugin.$plugin." : '';

        $view = config(
            "{$configPrefix}view.templates.system.$template",
            __DIR__ . "/templates/$template.phtml"
        );

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
