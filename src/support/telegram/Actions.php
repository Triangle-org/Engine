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

namespace support\telegram;

use ReflectionClass;
use support\telegram\Exceptions\TelegramSDKException;

/**
 * Class Actions.
 *
 * Chat Actions let you broadcast a type of action depending on what the user is about to receive.
 * The status is set for 5 seconds or less (when a message arrives from your bot, Telegram clients clear its typing
 * status).
 */
final class Actions
{
    /** Sets chat status as Typing.
     * @var string
     */
    public const TYPING = 'typing';

    /** Sets chat status as Sending Photo.
     * @var string
     */
    public const UPLOAD_PHOTO = 'upload_photo';

    /** Sets chat status as Recording Video.
     * @var string
     */
    public const RECORD_VIDEO = 'record_video';

    /** Sets chat status as Sending Video.
     * @var string
     */
    public const UPLOAD_VIDEO = 'upload_video';

    /**
     * @deprecated Please use RECORD_VOICE (the correct one)
     * Sets chat status as Recording Audio.
     *
     * @var string
     */
    public const RECORD_AUDIO = 'record_voice';

    /** Sets chat status as Recording Voice.
     * @var string
     */
    public const RECORD_VOICE = 'record_voice';

    /**
     * @deprecated Please use UPLOAD_VOICE (the correct one)
     * Sets chat status as Sending Audio.
     *
     * @var string
     */
    public const UPLOAD_AUDIO = 'upload_voice';

    /** Sets chat status as Sending Voice.
     * @var string
     */
    public const UPLOAD_VOICE = 'upload_voice';

    /** Sets chat status as Sending Document.
     * @var string
     */
    public const UPLOAD_DOCUMENT = 'upload_document';

    /** Sets chat status as Choosing Sticker.
     * @var string
     */
    public const CHOOSE_STICKER = 'choose_sticker';

    /** Sets chat status as Choosing Geo.
     * @var string
     */
    public const FIND_LOCATION = 'find_location';

    /** Sets chat status as Recording Video Note.
     * @var string
     */
    public const RECORD_VIDEO_NOTE = 'record_video_note';

    /** Sets chat status as Sending Video Note.
     * @var string
     */
    public const UPLOAD_VIDEO_NOTE = 'upload_video_note';

    public static function all(): array
    {
        return (new ReflectionClass(self::class))->getConstants();
    }

    public static function isActionValid(string $action): bool
    {
        $actions = self::all();
        if (in_array($action, $actions, true)) {
            return true;
        }

        throw new TelegramSDKException('Invalid Action! Accepted value: ' . implode(', ', $actions));
    }
}
