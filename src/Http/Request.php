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

use Triangle\Engine\Router\Route;
use function current;
use function filter_var;
use function is_array;
use const FILTER_VALIDATE_IP;

/**
 * Класс Request
 * Этот класс представляет собой пользовательский запрос, который наследует от базового класса Http\Request.
 */
class Request extends \localzet\Server\Protocols\Http\Request
{
    /**
     * @var string|null
     */
    public ?string $plugin = null;

    /**
     * @var string|null
     */
    public ?string $app = null;

    /**
     * @var string|null
     */
    public ?string $controller = null;

    /**
     * @var string|null
     */
    public ?string $action = null;

    /**
     * @var Route|null
     */
    public ?Route $route = null;

    /**
     * Получить файл из запроса
     * @param string|null $name Имя файла
     * @return \File|File[]|null
     */
    public function file($name = null): array|\File|null
    {
        $files = parent::file($name);
        if (null === $files) {
            return $name === null ? [] : null;
        }
        if ($name !== null) {
            // Multi files
            if (is_array(current($files))) {
                return $this->parseFiles($files);
            }
            return $this->parseFile($files);
        }
        $uploadFiles = [];
        foreach ($files as $name => $file) {
            // Multi files
            if (is_array(current($file))) {
                $uploadFiles[$name] = $this->parseFiles($file);
            } else {
                $uploadFiles[$name] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * Разобрать массив файлов
     * @param array $files Массив файлов
     * @return array
     */
    protected function parseFiles(array $files): array
    {
        $uploadFiles = [];
        foreach ($files as $key => $file) {
            if (is_array(current($file))) {
                $uploadFiles[$key] = $this->parseFiles($file);
            } else {
                $uploadFiles[$key] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * Разобрать файл
     * @param array $file Файл
     * @return File
     */
    protected function parseFile(array $file): File
    {
        return new File($file['tmp_name'], $file['name'], $file['type'], $file['error']);
    }

    /**
     * Получить реальный IP-адрес клиента
     * @param bool $safeMode Безопасный режим
     * @return string
     */
    public function getRealIp(bool $safeMode = true): string
    {
        $remoteIp = $this->getRemoteIp();
        if ($safeMode && !$this->isIntranet()) {
            return $remoteIp;
        }
        $ip = $this->header('x-real-ip', $this->header(
            'x-forwarded-for',
            $this->header('client-ip', $this->header(
                'x-client-ip',
                $this->header('via', $remoteIp)
            ))
        ));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $remoteIp;
    }

    /**
     * @return bool
     */
    protected function isIntranet(): bool
    {
        $ip = $this->getRemoteIp();

        // Не IP.
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        // Точно ip Интранета? Для IPv4 FALSE может быть не точным, поэтому нам нужно проверить его вручную ниже.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        // Ручная проверка IPv4.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        // Ручная проверка
        // $reservedIps = [
        //     '167772160'  => 184549375,  // 10.0.0.0 -  10.255.255.255
        //     '3232235520' => 3232301055, // 192.168.0.0 - 192.168.255.255
        //     '2130706432' => 2147483647, // 127.0.0.0 - 127.255.255.255
        //     '2886729728' => 2887778303, // 172.16.0.0 -  172.31.255.255
        // ];

        $reservedIps = [
            1681915904 => 1686110207,   // 100.64.0.0 -  100.127.255.255
            3221225472 => 3221225727,   // 192.0.0.0 - 192.0.0.255
            3221225984 => 3221226239,   // 192.0.2.0 - 192.0.2.255
            3227017984 => 3227018239,   // 192.88.99.0 - 192.88.99.255
            3323068416 => 3323199487,   // 198.18.0.0 - 198.19.255.255
            3325256704 => 3325256959,   // 198.51.100.0 - 198.51.100.255
            3405803776 => 3405804031,   // 203.0.113.0 - 203.0.113.255
            3758096384 => 4026531839,   // 224.0.0.0 - 239.255.255.255
        ];

        $ipLong = ip2long($ip);

        foreach ($reservedIps as $ipStart => $ipEnd) {
            if (($ipLong >= $ipStart) && ($ipLong <= $ipEnd)) {
                return true;
            }
        }

        return false;
    }
}
