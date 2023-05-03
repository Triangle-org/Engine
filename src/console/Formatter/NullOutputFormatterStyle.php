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

namespace Triangle\Engine\Console\Formatter;

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
