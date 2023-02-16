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
final class NullOutputFormatterStyle implements OutputFormatterStyleInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(string $text): string
    {
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground(string $color = null): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function setForeground(string $color = null): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(string $option): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function unsetOption(string $option): void
    {
        // do nothing
    }
}
