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
 * Class ChatInviteLink.
 *
 * @link https://core.telegram.org/bots/api#chatinvitelink
 *
 * @property string $invite_link                The invite link. If the link was created by another chat administrator, then the second part of the link will be replaced with “…”.
 * @property User $creator                    Creator of the link.
 * @property bool $creates_join_request       True, if users joining the chat via the link need to be approved by chat administrators.
 * @property bool $is_primary                 True, if the link is primary.
 * @property bool $is_revoked                 True, if the link is revoked.
 * @property string|null $name                       (Optional). Invite link name.
 * @property int|null $expire_date                (Optional). Point in time (Unix timestamp) when the link will expire or has been expired.
 * @property int|null $member_limit               (Optional). Maximum number of users that can be members of the chat simultaneously after joining the chat via this invite link; 1-99999.
 * @property int|null $pending_join_request_count (Optional). Number of pending join requests created using this link.
 */
class ChatInviteLink extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'creator' => User::class,
        ];
    }
}
