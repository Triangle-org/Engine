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

namespace support\telegram\Keyboard;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Base.
 *
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
class Base extends Collection
{
    /**
     * Dynamically build params.
     *
     * @param string $method
     * @param array $args
     *
     * @return $this
     */
    public function __call($method, $args)
    {
        if (!Str::startsWith($method, 'set')) {
            return parent::__call($method, $args);
        }
        $property = Str::snake(substr($method, 3));
        $this->items[$property] = $args[0];

        return $this;
    }
}
