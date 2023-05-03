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
 * Class InputVenueMessageContent.
 *
 * Represents the content of a venue message to be sent as the result of an inline query.
 *
 * <code>
 * [
 *   'latitude'          => '',  //  float   - Required. Latitude of the location in degrees
 *   'longitude'         => '',  //  float   - Required. Longitude of the location in degrees
 *   'title'             => '',  //  string  - Required. Name of the venue
 *   'address'           => '',  //  string  - Required. Address of the venue
 *   'foursquare_id'     => '',  //  string  - (Optional). Foursquare identifier of the venue, if known
 *   'foursquare_type'   => '',  //  string  - (Optional). Foursquare type of the venue, if known. (For example, “arts_entertainment/default”, “arts_entertainment/aquarium” or “food/icecream”.)
 *   'google_place_id'   => '',  //  string  - (Optional). Google Places identifier of the venue
 *   'google_place_type' => '',  //  string  - (Optional). Google Places type of the venue.
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inputvenuemessagecontent
 *
 * @method $this setLatitude(float)Latitude of the location in degrees
 * @method $this setLongitude(float)Longitude of the location in degrees
 * @method $this setTitle(string)Name of the venue
 * @method $this setAddress(string)Address of the venue
 * @method $this setFoursquareIdTitle(string)(Optional). Foursquare identifier of the venue, if known
 * @method $this setFoursquareType(string)(Optional). Foursquare type of the venue, if known. (For example, “arts_entertainment/default”, “arts_entertainment/aquarium” or “food/icecream”.)
 */
class InputVenueMessageContent extends InlineBaseObject
{
}
