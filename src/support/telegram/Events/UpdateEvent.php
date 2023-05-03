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

final class UpdateEvent extends AbstractEvent
{
    public const NAME = 'update';

    /**
     * @deprecated Will be removed in SDK v4
     *
     * @var string
     */
    private $name;

    /** @var \support\telegram\Api */
    public $telegram;

    /** @var \support\telegram\Objects\Update */
    public $update;

    public function __construct(Api $telegram, Update $update, string $name = self::NAME)
    {
        $this->telegram = $telegram;
        $this->update = $update;
        $this->name = $name;
    }

    /** {@inheritDoc} */
    public function getName(): string
    {
        return $this->name;
    }
}
