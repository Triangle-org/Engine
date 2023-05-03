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

namespace support\telegram\Methods;

use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\Objects\BotCommand;
use support\telegram\Traits\Http;

/**
 * Class Commands.
 *
 * @mixin Http
 */
trait Commands
{
    /**
     * Change the list of the bots commands.
     *
     * <code>
     * $params = [
     *      'commands'      => '',  // array           - Required. A JSON-serialized list of bot commands to be set as the list of the bot's commands. At most 100 commands can be specified.
     *      'scope'         => '',  // BotCommandScope - (Optional). A JSON-serialized object, describing scope of users for which the commands are relevant. Defaults to BotCommandScopeDefault.
     *      'language_code' => '',  // String          - (Optional). A two-letter ISO 639-1 language code. If empty, commands will be applied to all users from the given scope, for whose language there are no dedicated commands
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setmycommands
     *
     * @param array $params Where "commands" key is required, where value is a serialized array of commands.
     *
     * @throws TelegramSDKException
     */
    public function setMyCommands(array $params): bool
    {
        $params['commands'] = is_string($params['commands'])
            ? $params['commands']
            : json_encode($params['commands'], JSON_THROW_ON_ERROR);

        return $this->post('setMyCommands', $params)->getResult();
    }

    /**
     * Delete the list of the bot's commands for the given scope and user language
     *
     * <code>
     * $params = [
     *      'scope'         => '',  // BotCommandScope - (Optional). A JSON-serialized object, describing scope of users for which the commands are relevant. Defaults to BotCommandScopeDefault.
     *      'language_code' => '',  // String          - (Optional). A two-letter ISO 639-1 language code. If empty, commands will be applied to all users from the given scope, for whose language there are no dedicated commands
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#deletemycommands
     *
     * @param mixed[] $params
     */
    public function deleteMyCommands(array $params = []): bool
    {
        return $this->post('deleteMyCommands', $params)->getResult();
    }

    /**
     * Get the current list of the bot's commands.
     *
     * <code>
     * $params = [
     *      'scope'         => '',  // BotCommandScope - (Optional). A JSON-serialized object, describing scope of users. Defaults to BotCommandScopeDefault.
     *      'language_code' => '',  // String          - (Optional). A two-letter ISO 639-1 language code or an empty string
     * ]
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getmycommands
     *
     * @return BotCommand[]
     *
     * @throws TelegramSDKException
     */
    public function getMyCommands(array $params = []): array
    {
        return collect($this->get('getMyCommands', $params)->getResult())
            ->mapInto(BotCommand::class)
            ->all();
    }
}
