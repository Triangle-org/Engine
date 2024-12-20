<?php

/**
 * @package     Triangle HTTP Component
 * @link        https://github.com/Triangle-org/Http
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
 *              For any questions, please contact <triangle@localzet.com>
 */

namespace Triangle\Exception;

use Throwable;
use Triangle\support\Request;
use Triangle\support\Response;

class PageNotFoundException extends NotFoundException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string    $message = '404 Not Found',
        int       $code = 404,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): ?Response
    {
        $json = [
            'status' => $this->getCode() ?? 404,
            'error' => $this->trans($this->getMessage(), $this->data),
            'data' => $this->data,
        ];

        if (config('app.debug')) {
            $json['debug'] = config('app.debug');
            $json['traces'] = nl2br((string)$this);
        }

        if ($request->expectsJson()) return responseJson($json);

        return responseView($json, 500);
    }
}