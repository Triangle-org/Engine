<?php declare(strict_types=1);
/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
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

use Throwable;
use function filemtime;
use function gmdate;

/**
 * Класс Response
 * Этот класс представляет собой пользовательский ответ, который наследует от базового класса Http\Response.
 *
 * @link https://www.php-fig.org/psr/psr-7
 * @link https://www.php.net/manual/en/class.httpresponse.php
 */
#[\AllowDynamicProperties]
class Response extends \localzet\Server\Protocols\Http\Response
{
    /**
     * @var Throwable|null $exception Исключение, связанное с ответом.
     */
    protected ?Throwable $exception = null;

    /**
     * Конструктор класса Response.
     *
     * @param int $status Статус-код HTTP.
     * @param array $headers Заголовки HTTP.
     * @param string $body Тело ответа.
     */
    public function __construct(
        int    $status = 200,
        array  $headers = [],
        string $body = ''
    )
    {
        $headers += config('app.http_headers', []);
        parent::__construct($status, $headers, $body);
    }

    /**
     * Получить или установить исключение.
     *
     * @param Throwable|null $throwable Исключение для установки.
     */
    public function exception(Throwable $throwable = null): ?Throwable
    {
        if ($throwable instanceof Throwable) {
            $this->exception = $throwable;
        }

        return $this->exception;
    }

    /**
     * Отправить файл в ответе.
     *
     * @param string $file Путь к файлу.
     * @return $this
     */
    public function file(string $file): self
    {
        if ($this->notModifiedSince($file)) {
            return $this->withStatus(304);
        }

        return $this->withFile($file);
    }

    /**
     * Проверить, был ли файл изменен с момента последнего запроса.
     *
     * @param string $file Путь к файлу.
     */
    protected function notModifiedSince(string $file): bool
    {
        $ifModifiedSince = request()->header('if-modified-since');
        if ($ifModifiedSince === null || !is_file($file) || !($mtime = filemtime($file))) {
            return false;
        }

        return $ifModifiedSince === gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

    /**
     * Загрузить файл.
     *
     * @param string $file Путь к файлу.
     * @param string $downloadName Имя файла для загрузки.
     * @return $this
     */
    public function download(string $file, string $downloadName = ''): self
    {
        $this->withFile($file);
        if ($downloadName) {
            $this->header('Content-Disposition', "attachment; filename=\"$downloadName\"");
        }

        return $this;
    }
}
