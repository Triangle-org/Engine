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

namespace support\jwt\lib;

use InvalidArgumentException;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use TypeError;
use function is_resource;
use function is_string;

class Key
{
    /** @var string|resource|OpenSSLAsymmetricKey|OpenSSLCertificate */
    private $keyMaterial;
    /** @var string */
    private string $algorithm;

    /**
     * @param OpenSSLAsymmetricKey|string|OpenSSLCertificate $keyMaterial
     * @param string $algorithm
     */
    public function __construct(
        OpenSSLAsymmetricKey|string|OpenSSLCertificate $keyMaterial,
        string                                         $algorithm
    )
    {
        if (
            !is_string($keyMaterial)
            && !$keyMaterial instanceof OpenSSLAsymmetricKey
            && !$keyMaterial instanceof OpenSSLCertificate
            && !is_resource($keyMaterial)
        ) {
            throw new TypeError('Ключ должен быть строкой, ресурсом или OpenSSLAsymmetricKey');
        }

        if (empty($keyMaterial)) {
            throw new InvalidArgumentException('Укажите ключ');
        }

        if (empty($algorithm)) {
            throw new InvalidArgumentException('Необходимо указать алгоритм');
        }

        $this->keyMaterial = $keyMaterial;
        $this->algorithm = $algorithm;
    }

    /** Алгоритм, действительный для этого ключа
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /** Ключ
     *
     * @return string|resource|OpenSSLAsymmetricKey|OpenSSLCertificate
     */
    public function getKeyMaterial()
    {
        return $this->keyMaterial;
    }
}
