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

namespace support\telegram\Exceptions;

/**
 * Class ResponseParameters.
 *
 * Contains information about why a request was unsuccessful.
 *
 * @link https://core.telegram.org/bots/api#responseparameters
 *
 * @property int $migrateToChatId (Optional). The group has been migrated to a supergroup with the specified identifier.
 *           This number may be greater than 32 bits and some programming languages may have difficulty/silent defects
 *           in interpreting it. But it is smaller than 52 bits, so a signed 64 bit integer or double-precision float
 *           type are safe for storing this identifier.
 * @property int $retryAfter      (Optional). In case of exceeding flood control, the number of seconds left to wait
 *           before the request can be repeated
 *
 * @TODO Integrate with exceptions / error handler.
 */
class ResponseParameters
{
}
