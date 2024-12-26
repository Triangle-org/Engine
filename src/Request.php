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

namespace Triangle\Engine;

/**
 * Класс Request
 * Этот класс представляет собой пользовательский запрос, который наследует от базового класса Http\Request.
 *
 * @link https://www.php-fig.org/psr/psr-7
 * @link https://www.php.net/manual/en/class.httprequest.php
 */
#[\AllowDynamicProperties]
class Request extends \localzet\Server\Protocols\Http\Request
{
    /**
     * @var string|null $plugin Имя плагина, связанного с запросом.
     */
    public ?string $plugin = null;

    /**
     * @var string|null $app Имя приложения, связанного с запросом.
     */
    public ?string $app = null;

    /**
     * @var string|null $controller Имя контроллера, связанного с запросом.
     */
    public ?string $controller = null;

    /**
     * @var string|null $action Имя действия, связанного с запросом.
     */
    public ?string $action = null;

    /**
     * Получить файл из запроса.
     *
     * @param string|null $name Имя файла.
     * @return File|File[]|null
     */
    public function file(?string $name = null): array|File|null
    {
        $files = parent::file($name);
        if ($files === null) {
            return $name === null ? [] : null;
        }
        if ($name !== null) {
            return is_array(current($files)) ? $this->parseFiles($files) : $this->parseFile($files);
        }

        return array_map(function ($file) {
            return is_array(current($file)) ? $this->parseFiles($file) : $this->parseFile($file);
        }, $files);
    }

    /**
     * Разобрать массив файлов.
     *
     * @param array $files Массив файлов.
     * @return array
     */
    protected function parseFiles(array $files): array
    {
        return array_map(function ($file) {
            return is_array(current($file)) ? $this->parseFiles($file) : $this->parseFile($file);
        }, $files);
    }

    /**
     * Разобрать файл.
     *
     * @param array $file Файл.
     * @return File
     */
    protected function parseFile(array $file): File
    {
        return new File($file['tmp_name'], $file['name'], $file['type'], $file['error']);
    }
}