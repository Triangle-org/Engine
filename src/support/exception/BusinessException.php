<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\exception;

use Exception;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Http\Request;
use function json_encode;

/**
 * Class BusinessException
 */
class BusinessException extends Exception
{
    public function render(Request $request): ?Response
    {
        $json = [
            'debug' => (string)config('app.debug', false),
            'status' => $this->getCode() ?? 500,
            'error' => $this->getMessage(),
            'data' => config('app.debug', false) ? \nl2br((string)$this) : $this->getMessage(),
        ];
        config('app.debug', false) && $json['traces'] = (string)$this;

        if ($request->expectsJson()) return responseJson($json);

        return responseView($json, 500);
    }
}
