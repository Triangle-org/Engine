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

namespace support;

use Symfony\Component\Translation\Translator;
use Triangle\Engine\Exception\NotFoundException;
use function basename;
use function config;
use function get_realpath;
use function glob;
use function pathinfo;
use function request;
use function substr;

/**
 * Class Translation
 * @method static string trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
 * @method static void setLocale(string $locale)
 * @method static string getLocale()
 */
class Translation
{

    /**
     * @var Translator[]
     */
    protected static array $instance = [];

    /**
     * @param string $plugin
     * @return Translator
     * @throws NotFoundException
     */
    public static function instance(string $plugin = ''): Translator
    {
        if (!isset(static::$instance[$plugin])) {
            $config = config($plugin ? "plugin.$plugin.translation" : 'translation', []);
            // Phar support. Compatible with the 'realpath' function in the phar file.
            if (!$translationsPath = get_realpath($config['path'])) {
                throw new NotFoundException("File {$config['path']} not found");
            }

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

            foreach ($classes as $class => $opts) {
                $translator->addLoader($opts['format'], new $class);
                foreach (glob($translationsPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . $opts['extension']) as $file) {
                    $domain = basename($file, $opts['extension']);
                    $dirName = pathinfo($file, PATHINFO_DIRNAME);
                    $locale = substr(strrchr($dirName, DIRECTORY_SEPARATOR), 1);
                    if ($domain && $locale) {
                        $translator->addResource($opts['format'], $file, $locale, $domain);
                    }
                }
            }
        }
        return static::$instance[$plugin];
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws NotFoundException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        return static::instance($plugin)->{$name}(...$arguments);
    }
}
