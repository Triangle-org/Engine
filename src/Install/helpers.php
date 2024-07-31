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

use support\Response;
use support\Translation;

$install_path = Composer\InstalledVersions::getRootPackage()['install_path'] ?? null;
define('BASE_PATH', str_starts_with($install_path, 'phar://') ? $install_path : realpath($install_path) ?? dirname(__DIR__));

/** RESPONSE HELPERS */

/**
 * @param mixed $body
 * @param int $status
 * @param array $headers
 * @param bool $http_status
 * @param bool $onlyJson
 * @return Response
 * @throws Throwable
 */
function response(mixed $body = '', int $status = 200, array $headers = [], bool $http_status = false, bool $onlyJson = false): Response
{
    $status = ($http_status === true) ? $status : 200;
    $body = [
        'status' => $status,
        'data' => $body
    ];

    if (config('app.debug')) {
        $body['debug'] = config('app.debug');
    }

    if (!function_exists('responseView') || request()->expectsJson() || $onlyJson) {
        return responseJson($body, $status, $headers);
    } else {
        return responseView($body, $status, $headers);
    }
}

/**
 * @param string $blob
 * @param string $type
 * @return Response
 */
function responseBlob(string $blob, string $type = 'image/png'): Response
{
    return new Response(200, ['Content-Type' => $type], $blob);
}

/**
 * @param $data
 * @param int $status
 * @param array $headers
 * @param int $options
 * @return Response
 */
function responseJson($data, int $status = 200, array $headers = [], int $options = JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR): Response
{
    return new Response($status, ['Content-Type' => 'application/json'] + $headers, json($data, $options));
}

/**
 * @param string $location
 * @param int $status
 * @param array $headers
 * @return Response
 */
function redirect(string $location, int $status = 302, array $headers = []): Response
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }
    return $response;
}

/**
 * 404 not found
 *
 * @return Response
 * @throws Throwable
 */
function not_found(): Response
{
    return response('Ничего не найдено', 404);
}


/** TRANSLATION HELPERS */


/**
 * Translation
 * @param string $id
 * @param array $parameters
 * @param string|null $domain
 * @param string|null $locale
 * @return string
 */
function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
{
    $res = Translation::trans($id, $parameters, $domain, $locale);
    return $res === '' ? $id : $res;
}

/**
 * Locale
 * @param string|null $locale
 * @return string
 */
function locale(string $locale = null): string
{
    if (!$locale) {
        return Translation::getLocale();
    }
    Translation::setLocale($locale);
    return $locale;
}



