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

declare(strict_types=1);

namespace support\jwt;

use app\exception\ResponseException;
use DomainException;
use InvalidArgumentException;
use support\jwt\Exception\BeforeValidException;
use support\jwt\Exception\ExpiredException;
use support\jwt\Exception\SignatureInvalidException;
use support\jwt\lib\JWT;
use support\jwt\lib\Key;
use UnexpectedValueException;

class Generator
{
    private const ACCESS_TOKEN = 1;
    private const REFRESH_TOKEN = 2;


    /** Обновление токена
     * @param string $token refresh_token
     * @return array 'access' => access_token, 'refresh' => refresh_token
     */
    public static function refresh(string $token): array
    {
        $data = self::decode($token, self::REFRESH_TOKEN);
        return self::encode($data);
    }

    /** Генерация токенов
     * @param array $data Данные токена
     * @return array 'access' => access_token, 'refresh' => refresh_token
     */
    public static function encode(array $data): array
    {
        $payload = self::payload($data);
        $accessKey = self::key();
        $refreshKey = self::key(self::REFRESH_TOKEN);

        return [
            'access' => JWT::encode($payload['access'], $accessKey['private'], config('jwt.algorithms')),
            'refresh' => JWT::encode($payload['refresh'], $refreshKey['private'], config('jwt.algorithms')),
        ];
    }

    /** Расшифровка токена
     * @param string $token access_token | refresh_token
     * @param int $type self::ACCESS_TOKEN(1) | self::REFRESH_TOKEN(2)
     * @return array Данные токена
     *
     * @throws InvalidArgumentException     Ключ пуст или некорректен
     * @throws DomainException              JWT имеет неверный формат
     * @throws UnexpectedValueException     JWT некорректен
     * @throws SignatureInvalidException    Ошибка проверки подписи
     * @throws BeforeValidException         Использование JWT до даты, указанной в «nbf»
     * @throws BeforeValidException         Использование JWT до даты, указанной в «iat»
     * @throws ExpiredException             Использование JWT после даты, указанной в «exp»
     */
    public static function decode(string $token, int $type = self::ACCESS_TOKEN, bool $timeException = true): array
    {
        try {
            $key = self::key($type);
            JWT::$leeway = config('jwt.leeway');

            $decoded = JWT::decode($token, new Key($key['public'], config('jwt.algorithms')), $timeException);
            $result = json_decode(json_encode($decoded), true);
        } catch (ExpiredException) {
            throw new ResponseException('jwt-exp');
        }
        return $result;
    }

    /** Генерация полезной нагрузки
     * @param array $data 'iss', 'sub', 'aud', 'nbf', 'iat', 'jti', 'access_exp', 'refresh_exp'
     * @return array 'access' => access_payload, 'refresh' => refresh_payload
     */
    private static function payload(array $data): array
    {
        $exp = $data['exp'] ?? true;
        if ($exp !== false) {
            $access_exp = time() + ($data['access_exp'] ?? config('jwt.access_exp'));
            $refresh_exp = time() + ($data['refresh_exp'] ?? config('jwt.refresh_exp'));
        }

        $allow = ['iss', 'sub', 'aud', 'nbf', 'iat', 'jti'];
        $disallow = ['access_exp', 'refresh_exp', 'exp'];
        $payload = [
            'iss' => 'Triangle JWT Module',
            'sub' => 'Verify Key',
            'aud' => config('app.name', 'Triangle App'),
            'nbf' => time(),
            'iat' => time(),
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $disallow)) {
                continue;
            }
            if (in_array($key, $allow) && $value !== false) {
                $payload[$key] = $value;
            } else {
                $payload['data'][$key] = $value;
            }
        }
        if ($exp !== false) {
            $result['access'] = $payload + ['exp' => $access_exp];
            $result['refresh'] = $payload + ['exp' => $refresh_exp];
        } else {
            $result['access'] = $payload;
            $result['refresh'] = $payload;
        }

        return $result;
    }

    /** Генерация ключа
     * @param int $type self::ACCESS_TOKEN(1) | self::REFRESH_TOKEN(2)
     * @return array 'public' => public_key, 'private' => private_key
     */
    private static function key(int $type = self::ACCESS_TOKEN): array
    {
        switch (config('jwt.algorithms')) {
            case 'RS512':
            case 'RS256':
                $public = self::ACCESS_TOKEN == $type ? config('jwt.access_public_key') : config('jwt.refresh_public_key');
                $private = self::ACCESS_TOKEN == $type ? config('jwt.access_private_key') : config('jwt.refresh_private_key');
                break;
            default:
                $public = $private = self::ACCESS_TOKEN == $type ? config('jwt.access_secret_key') : config('jwt.refresh_secret_key');
        }

        return ['public' => $public, 'private' => $private];
    }
}
