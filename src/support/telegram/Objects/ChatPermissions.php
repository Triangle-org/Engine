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

namespace support\telegram\Objects;

/**
 * Class ChatPermissions.
 *
 * @link https://core.telegram.org/bots/api#chatpermissions
 *
 * @property bool|null $canSendMessages       (Optional). True, if the user is allowed to send text messages, contacts, locations and venues
 * @property bool|null $canSendMediaMessages  (Optional). True, if the user is allowed to send audios, documents, photos, videos, video notes and voice notes, implies can_send_messages
 * @property bool|null $canSendPolls          (Optional). True, if the user is allowed to send polls, implies can_send_messages
 * @property bool|null $canSendOtherMessages  (Optional). True, if the user is allowed to send animations, games, stickers and use inline bots, implies can_send_media_messages
 * @property bool|null $canAddWebPagePreviews (Optional). True, if the user is allowed to add web page previews to their messages, implies can_send_media_messages
 * @property bool|null $canChangeInfo         (Optional). True, if the user is allowed to change the chat title, photo and other settings. Ignored in public supergroups
 * @property bool|null $canInviteUsers        (Optional). True, if the user is allowed to invite new users to the chat
 * @property bool|null $canPinMessages        (Optional). True, if the user is allowed to pin messages. Ignored in public supergroups
 */
class ChatPermissions extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
