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

namespace support\exception;

use Exception;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use function nl2br;

/**
 * Class BusinessException
 */
class BusinessException extends Exception
{
    /**
     * @throws \Throwable
     */
    public function render(Request $request): ?Response
    {
        $json = [
            'debug' => (string)config('app.debug', false),
            'status' => $this->getCode() ?? 500,
            'error' => $this->getMessage(),
            'data' => config('app.debug', false) ? nl2br((string)$this) : $this->getMessage(),
        ];
        config('app.debug', false) && $json['traces'] = (string)$this;

        if ($request->expectsJson()) return responseJson($json);

        return responseView($json, 500);
    }
}
