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

namespace support\telegram\Objects\InlineQuery;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class InlineBaseObject.
 */
abstract class InlineBaseObject extends Collection
{
    /** @var string Type */
    protected $type;

    /**
     * InlineBaseObject constructor.
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->put('type', $this->type);
    }

    /**
     * Magic method to set properties dynamically.
     *
     * @return $this|mixed
     */
    public function __call($name, $arguments)
    {
        if (!Str::startsWith($name, 'set')) {
            throw new BadMethodCallException(sprintf('Method %s does not exist.', $name));
        }

        $property = Str::snake(substr($name, 3));
        $this->put($property, $arguments[0]);

        return $this;
    }
}
