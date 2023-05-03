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

/**
 * Class EntityType.
 */
final class EntityType
{
    /** Sets MessageEntity Type as mention.
     * @var string
     */
    public const MENTION = 'mention';

    /** Sets MessageEntity Type as hashtag .
     * @var string
     */
    public const HASHTAG = 'hashtag';

    /** Sets MessageEntity Type as cashtag.
     * @var string
     */
    public const CASHTAG = 'cashtag';

    /** Sets MessageEntity Type as Bot Command.
     * @var string
     */
    public const BOT_COMMAND = 'bot_command';

    /** Sets MessageEntity Type as url.
     * @var string
     */
    public const URL = 'url';

    /** Sets MessageEntity Type as email.
     * @var string
     */
    public const EMAIL = 'email';

    /** Sets MessageEntity Type as phone number.
     * @var string
     */
    public const PHONE_NUMBER = 'phone_number';

    /** Sets MessageEntity Type as bold.
     * @var string
     */
    public const BOLD = 'bold';

    /** Sets MessageEntity Type as italic.
     * @var string
     */
    public const ITALIC = 'italic';

    /** Sets MessageEntity Type as underline.
     * @var string
     */
    public const UNDERLINE = 'underline';

    /** Sets MessageEntity Type as strike through.
     * @var string
     */
    public const STRIKETHROUGH = 'strikethrough';

    /** Sets MessageEntity Type as spoiler .
     * @var string
     */
    public const SPOILER = 'spoiler';

    /** Sets MessageEntity Type as code.
     * @var string
     */
    public const CODE = 'code';

    /** Sets MessageEntity Type as pre.
     * @var string
     */
    public const PRE = 'code';

    /** Sets MessageEntity Type as text link.
     * @var string
     */
    public const TEXT_LINK = 'text_link';

    /** Sets MessageEntity Type as text mention.
     * @var string
     */
    public const TEXT_MENTION = 'text_mention';
}
