<?php

namespace Triangle\Engine\View;

use Triangle\Engine\App;

/**
 * Абстрактный класс AbstractView
 * Этот класс реализует интерфейс ViewInterface и предоставляет базовую функциональность для представлений.
 */
abstract class AbstractView implements ViewInterface
{
    /**
     * @var array Массив переменных представления
     */
    protected static array $vars = [];

    /**
     * @var array Массив для предварительного рендеринга
     */
    protected static array $preRender = [];

    /**
     * @var array Массив для пост-рендеринга
     */
    protected static array $postRender = [];

    /**
     * Присваивает значение переменной представления
     * @param array|string $name Имя переменной или массив переменных
     * @param mixed|null $value Значение переменной
     * @param bool $merge_recursive Флаг для рекурсивного слияния
     */
    public static function assign(array|string $name, mixed $value = null, bool $merge_recursive = false): void
    {
        if ($merge_recursive) {
            static::$vars = array_merge_recursive(static::$vars, is_array($name) ? $name : [$name => $value]);
        } else {
            static::$vars = array_merge(static::$vars, is_array($name) ? $name : [$name => $value]);
        }
    }

    /**
     * Возвращает все переменные представления
     * @return array
     */
    public static function vars(): array
    {
        return static::$vars;
    }

    /**
     * Строит представление с заданными параметрами
     * @param array $params Параметры представления
     * @return string
     */
    public static function build(array $params): string
    {
        $request = App::request();
        $template = $params['template'];
        $app = $params['app'] ?? null;
        $plugin = $params['plugin'] ?? null;

        $app = $app === null ? ($request->app ?? '') : $app;
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;

        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $baseViewPath = $plugin ? base_path("plugin/$plugin/app") : app_path();
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');

        return
            $app === '' ?
                "$baseViewPath/view/$template.$viewSuffix" :
                "$baseViewPath/$app/view/$template.$viewSuffix";
    }

    /**
     * Добавляет шаблон для предварительного рендеринга
     * @param string $template Шаблон для рендеринга
     * @param string|null $app Приложение
     * @param string|null $plugin Плагин
     */
    public static function addPreRender(string $template, string $app = null, string $plugin = null): void
    {
        self::$preRender[] = [
            'template' => $template,
            'app' => $app,
            'plugin' => $plugin
        ];
    }

    /**
     * Возвращает все шаблоны для предварительного рендеринга
     * @return array
     */
    public static function getPreRenders(): array
    {
        return array_unique(self::$preRender);
    }

    /**
     * Добавляет шаблон для пост-рендеринга
     * @param string $template Шаблон для рендеринга
     * @param string|null $app Приложение
     * @param string|null $plugin Плагин
     */
    public static function addPostRender(string $template, string $app = null, string $plugin = null): void
    {
        self::$postRender[] = [
            'template' => $template,
            'app' => $app,
            'plugin' => $plugin
        ];
    }

    /**
     * Возвращает все шаблоны для пост-рендеринга
     * @return array
     */
    public static function getPostRenders(): array
    {
        return array_unique(self::$preRender);
    }
}