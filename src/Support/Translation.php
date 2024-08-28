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

namespace support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RuntimeException;
use Symfony\Component\Translation\Translator;
use function basename;
use function config;
use function get_realpath;
use function pathinfo;
use function substr;

/**
 * Класс Translation
 * Этот класс предоставляет статические методы для работы с переводами.
 *
 * @link https://symfony.com/doc/current/translation.html
 *
 * Методы:
 * @method static string trans(?string $id, array $parameters = [], string $domain = null, string $locale = null) Переводит сообщение.
 * @method static void setLocale(string $locale) Устанавливает локаль для перевода.
 * @method static string getLocale() Получает текущую локаль для перевода.
 */
class Translation
{

    /**
     * @var Translator[] $instance Экземпляры переводчика для каждого плагина.
     */
    protected static array $instance = [];

    /**
     * Магический метод для вызова методов переводчика.
     *
     * @param string $name Имя метода.
     * @param array $arguments Аргументы метода.
     * @return mixed Результат вызова метода.
     * @throws RuntimeException Если файл перевода не найден.
     *
     * @link https://www.php.net/manual/ru/language.oop5.overloading.php#object.callstatic
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        return static::instance($plugin)->{$name}(...$arguments);
    }

    /**
     * Метод для получения экземпляра переводчика.
     *
     * @param string $plugin Имя плагина.
     * @return Translator Экземпляр переводчика.
     * @throws RuntimeException Если файл перевода не найден.
     *
     * @link https://symfony.com/doc/current/translation.html
     */
    public static function instance(string $plugin = ''): Translator
    {
        if (!isset(static::$instance[$plugin])) {
            $config = config($plugin ? config('app.plugin_alias', 'plugin') . ".$plugin.translation" : 'translation', []);
            $paths = (array)($config['path'] ?? []);

            static::$instance[$plugin] = $translator = new Translator($config['locale']);
            $translator->setFallbackLocales($config['fallback_locale']);

            $classes = [
                'Symfony\Component\Translation\Loader\PhpFileLoader' => [
                    'extension' => '.php',
                    'format' => 'phpfile'
                ],
                'Symfony\Component\Translation\Loader\PoFileLoader' => [
                    'extension' => '.po',
                    'format' => 'pofile'
                ]
            ];

            foreach ($paths as $path) {
                if (!$translationsPath = get_realpath($path)) {
                    throw new RuntimeException("File $path not found");
                }

                foreach ($classes as $class => $opts) {
                    $translator->addLoader($opts['format'], new $class);
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($translationsPath, FilesystemIterator::SKIP_DOTS));
                    $files = new RegexIterator($iterator, '/^.+' . preg_quote($opts['extension']) . '$/i', RegexIterator::GET_MATCH);
                    foreach ($files as $file) {
                        $file = $file[0];
                        $domain = basename($file, $opts['extension']);
                        $dirName = pathinfo($file, PATHINFO_DIRNAME);
                        $locale = substr(strrchr($dirName, DIRECTORY_SEPARATOR), 1);
                        if ($domain && $locale) {
                            $translator->addResource($opts['format'], $file, $locale, $domain);
                        }
                    }
                }
            }
        }
        return static::$instance[$plugin];
    }
}
