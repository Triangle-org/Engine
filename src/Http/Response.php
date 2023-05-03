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

namespace Triangle\Engine\Http;

use Throwable;
use Triangle\Engine\App;
use function filemtime;
use function gmdate;


/**
 * Class Response
 */
class Response extends \localzet\Server\Protocols\Http\Response
{
    /**
     * @var Throwable|null
     */
    protected ?Throwable $exception = null;

    function __construct(
        $status = 200,
        $headers = array(),
        $body = ''
    )
    {
        $headers = $headers + config('app.headers', []);
        parent::__construct($status, $headers, $body);
    }

    /**
     * @param string $file
     * @return $this
     */
    public function file(string $file): Response
    {
        if ($this->notModifiedSince($file)) {
            return $this->withStatus(304);
        }
        return $this->withFile($file);
    }

    /**
     * @param string $file
     * @param string $downloadName
     * @return $this
     */
    public function download(string $file, string $downloadName = ''): Response
    {
        $this->withFile($file);
        if ($downloadName) {
            $this->header('Content-Disposition', "attachment; filename=\"$downloadName\"");
        }
        return $this;
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function notModifiedSince(string $file): bool
    {
        $ifModifiedSince = App::request()->header('if-modified-since');
        if ($ifModifiedSince === null || !($mtime = filemtime($file))) {
            return false;
        }
        return $ifModifiedSince === gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

    /**
     * @param Throwable|null $exception
     * @return Throwable|null
     */
    public function exception(Throwable $exception = null): ?Throwable
    {
        if ($exception) {
            $this->exception = $exception;
        }
        return $this->exception;
    }
}
