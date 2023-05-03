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
 * Class InputContactMessageContent.
 *
 * Represents the content of a contact message to be sent as the result of an inline query.
 *
 * <code>
 * [
 *   'phone_number'  => '',  //  string  - Required. Contact's phone number
 *   'first_name'    => '',  //  string  - Required. Contact's first name
 *   'last_name'     => '',  //  string  - (Optional). Contact's last name
 *   'vcard'         => '',  //  string  - (Optional). Additional data about the contact in the form of a vCard, 0-2048 bytes
 * ]
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inputcontactmessagecontent
 *
 * @method $this setPhoneNumber(string)Contact's phone number
 * @method $this setFirstName(string)Contact's first name
 * @method $this setLastName(string)(Optional). Contact's last name
 * @method $this setVcard(string)(Optional). Additional data about the contact in the form of a vCard, 0-2048 bytes
 */
class InputContactMessageContent extends InlineBaseObject
{
}
