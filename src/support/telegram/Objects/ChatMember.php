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
 * Class ChatMember.
 *
 * @link https://core.telegram.org/bots/api#chatmember
 *
 * @property User $user                            Information about the user.
 * @property string $status                          The member's status in the chat. Can be “creator”, “administrator”, “member”, “left” or “kicked”
 * @property string|null $customTitle                     (Optional). Owner and administrators only. Custom title for this user
 * @property bool|null $isAnonymous                     (Optional). Owner and administrators only. True, if the user's presence in the chat is hidden
 * @property int|null $untilDate                       (Optional). Restricted and kicked only. Date when restrictions will be lifted for this user, unix time
 * @property bool|null $canBeEdited                     (Optional). Administrators only. True, if the bot is allowed to edit administrator privileges of that user
 * @property bool|null $canPostMessages                 (Optional). Administrators only. True, if the administrator can post in the channel, channels only
 * @property bool|null $canEditMessages                 (Optional). Administrators only. True, if the administrator can edit messages of other users, channels only
 * @property bool|null $canDeleteMessages               (Optional). Administrators only. True, if the administrator can delete messages of other users
 * @property bool|null $canPromoteMembers               (Optional). Administrators only. True, if the administrator can add new administrators with a subset of his own privileges or demote administrators that he has promoted, directly or indirectly (promoted by administrators that were appointed by the user)
 * @property bool|null $canChangeInfo                   (Optional). Administrators and restricted only. True, if the administrator can change the chat title, photo and other settings
 * @property bool|null $canInviteUsers                  (Optional). Administrators and restricted only. True, if the administrator can invite new users to the chat
 * @property bool|null $canPinMessages                  (Optional). Administrators and restricted only. True, if the administrator can pin messages, supergroups only
 * @property bool|null $isMember                        (Optional). Restricted only. True, if the user is a member of the chat at the moment of the request
 * @property bool|null $canSendMessages                 (Optional). Restricted only. True, if the user can send text messages, contacts, locations and venues
 * @property bool|null $canSendMediaMessages            (Optional). Restricted only. True, if the user can send audios, documents, photos, videos, video notes and voice notes, implies can_send_messages
 * @property bool|null $canSendPolls                    (Optional). Restricted only. True, if the user is allowed to send polls
 * @property bool|null $canSendOtherMessages            (Optional). Restricted only. True, if the user can send animations, games, stickers and use inline bots, implies can_send_media_messages
 * @property bool|null $canAddWebPagePreviews           (Optional). Restricted only. True, if user may add web page previews to his messages, implies can_send_media_messages
 * @property bool|null $canManageVoiceChats             (Optional). Restricted only. True, if user may manage voice chats
 * @property bool|null $canManageChat                   (Optional). Restricted only. True, if the administrator can access the chat event log, chat statistics, message statistics in channels, see channel members, see anonymous administrators in supergroups and ignore slow mode. Implied by any other administrator privilege
 */
class ChatMember extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'user' => User::class,
        ];
    }
}
