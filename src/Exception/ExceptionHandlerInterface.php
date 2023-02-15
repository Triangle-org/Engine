<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace Triangle\Engine\Exception;

use Throwable;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $exception
     * @return mixed
     */
    public function report(Throwable $exception);

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response;
}
