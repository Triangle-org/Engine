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

namespace support\telegram\Events;

use League\Event\AbstractEvent;
use support\telegram\Api;
use support\telegram\Objects\Update;

/**
 * Class UpdateWasReceived.
 */
class UpdateWasReceived extends AbstractEvent
{
    /** @var Update */
    private $update;

    /** @var Api */
    private $telegram;

    /**
     * UpdateWasReceived constructor.
     *
     * @param Update $update
     * @param Api $telegram
     */
    public function __construct(Update $update, Api $telegram)
    {
        $this->update = $update;
        $this->telegram = $telegram;
    }

    /**
     * @return Update
     */
    public function getUpdate(): Update
    {
        return $this->update;
    }

    /**
     * @return Api
     */
    public function getTelegram(): Api
    {
        return $this->telegram;
    }
}
