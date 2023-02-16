<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Formatter;

/**
 * @author Tien Xuan Vo <tien.xuan.vo@gmail.com>
 */
final class NullOutputFormatter implements OutputFormatterInterface
{
    private $style;

    /**
     * {@inheritdoc}
     */
    public function format(?string $message): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getStyle(string $name): OutputFormatterStyleInterface
    {
        // to comply with the interface we must return a OutputFormatterStyleInterface
        return $this->style ?? $this->style = new NullOutputFormatterStyle();
    }

    /**
     * {@inheritdoc}
     */
    public function hasStyle(string $name): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated(bool $decorated): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function setStyle(string $name, OutputFormatterStyleInterface $style): void
    {
        // do nothing
    }
}
