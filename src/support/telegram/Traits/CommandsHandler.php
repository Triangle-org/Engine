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

namespace support\telegram\Traits;

use Psr\Http\Message\RequestInterface;
use support\telegram\Commands\CommandBus;
use support\telegram\Exceptions\TelegramSDKException;
use support\telegram\Objects\Update;

/**
 * CommandsHandler.
 */
trait CommandsHandler
{
    /**
     * Return Command Bus.
     */
    public function getCommandBus(): CommandBus
    {
        return $this->commandBus;
    }

    public function setCommandBus(CommandBus $commandBus): static
    {
        $this->commandBus = $commandBus;

        return $this;
    }

    /**
     * Processes Inbound Commands.
     *
     * @return Update|Update[]
     */
    public function commandsHandler(bool $webhook = false, ?RequestInterface $request = null): Update|array
    {
        return $webhook ? $this->useWebHook($request) : $this->useGetUpdates();
    }

    /**
     * Process the update object for a command from your webhook.
     */
    protected function useWebHook(?RequestInterface $request = null): Update
    {
        $update = $this->getWebhookUpdate(true, $request);
        $this->processCommand($update);

        return $update;
    }

    /**
     * Process the update object for a command using the getUpdates method.
     *
     * @return Update[]
     *
     * @throws TelegramSDKException
     */
    protected function useGetUpdates(): array
    {
        $updates = $this->getUpdates();
        $highestId = -1;

        foreach ($updates as $update) {
            $highestId = $update->updateId;
            $this->processCommand($update);
        }

        //An update is considered confirmed as soon as getUpdates is called with an offset higher than it's update_id.
        if ($highestId !== -1) {
            $this->markUpdateAsRead($highestId);
        }

        return $updates;
    }

    /**
     * Mark updates as read.
     *
     * @return Update[]
     */
    protected function markUpdateAsRead($highestId): array
    {
        $params = [];
        $params['offset'] = $highestId + 1;
        $params['limit'] = 1;

        return $this->getUpdates($params, false);
    }

    /**
     * Check update object for a command and process.
     */
    public function processCommand(Update $update): void
    {
        $this->commandBus->handler($update);
    }

    /**
     * @param string $name Command Name
     * @param Update $update Update Object
     *
     * @deprecated This method will be protected and signature will be changed in SDK v4.
     * Helper to Trigger Commands.
     */
    public function triggerCommand(string $name, Update $update, array $entity = null): mixed
    {
        $entity ??= ['offset' => 0, 'length' => strlen($name) + 1, 'type' => 'bot_command'];

        return $this->commandBus->execute(
            $name,
            $update,
            $entity
        );
    }
}
