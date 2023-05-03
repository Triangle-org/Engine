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

namespace support\telegram\Objects\InputContent;

use support\telegram\Objects\InlineQuery\InlineBaseObject;

/**
 * Class InputLocationMessageContent.
 *
 * Represents the content of a location message to be sent as the result of an inline query.
 *
 * <code>
 * [
 *   'latitude'               => '',  //  float  - Required. Latitude of the location in degrees
 *   'longitude'              => '',  //  float  - Required. Longitude of the location in degrees
 *   'horizontal_accuracy'    => '',  //  float  - (Optional). The radius of uncertainty for the location, measured in meters; 0-1500
 *   'live_period'            => '',  //  int    - (Optional). Period in seconds for which the location can be updated, should be between 60 and 86400.
 *   'heading'                => '',  //  int    - (Optional). For live locations, a direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.
 *   'proximity_alert_radius' => '',  //  int    - (Optional). For live locations, a maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.
 * ]
 *
 * @link https://core.telegram.org/bots/api#inputlocationmessagecontent
 *
 * @method $this setLatitude(float)Latitude of the location in degrees
 * @method $this setLongitude(float)Longitude of the location in degrees
 * @method $this setLivePeriod(int)(Optional). Period in seconds for which the location can be updated, should be between 60 and 86400.
 */
class InputLocationMessageContent extends InlineBaseObject
{
}
