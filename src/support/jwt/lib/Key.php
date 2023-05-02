<?php

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
