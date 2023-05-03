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

namespace Triangle\Engine\Console\Helper;

use Triangle\Engine\Console\Exception\InvalidArgumentException;

/**
 * @author Yewhen Khoptynskyi <khoptynskyi@gmail.com>
 */
class TableCellStyle
{
    public const DEFAULT_ALIGN = 'left';

    private const TAG_OPTIONS = [
        'fg',
        'bg',
        'options',
    ];

    private const ALIGN_MAP = [
        'left' => \STR_PAD_RIGHT,
        'center' => \STR_PAD_BOTH,
        'right' => \STR_PAD_LEFT,
    ];

    private $options = [
        'fg' => 'default',
        'bg' => 'default',
        'options' => null,
        'align' => self::DEFAULT_ALIGN,
        'cellFormat' => null,
    ];

    public function __construct(array $options = [])
    {
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new InvalidArgumentException(sprintf('The TableCellStyle does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        if (isset($options['align']) && !\array_key_exists($options['align'], self::ALIGN_MAP)) {
            throw new InvalidArgumentException(sprintf('Wrong align value. Value must be following: \'%s\'.', implode('\', \'', array_keys(self::ALIGN_MAP))));
        }

        $this->options = array_merge($this->options, $options);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Gets options we need for tag for example fg, bg.
     *
     * @return string[]
     */
    public function getTagOptions()
    {
        return array_filter(
            $this->getOptions(),
            function ($key) {
                return \in_array($key, self::TAG_OPTIONS) && isset($this->options[$key]);
            },
            \ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return int
     */
    public function getPadByAlign()
    {
        return self::ALIGN_MAP[$this->getOptions()['align']];
    }

    public function getCellFormat(): ?string
    {
        return $this->getOptions()['cellFormat'];
    }
}
