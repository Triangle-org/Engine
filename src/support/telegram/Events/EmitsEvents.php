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

namespace support\telegram\Events;

use League\Event\Emitter;
use League\Event\EventInterface;

/**
 * EmitsEvents.
 */
trait EmitsEvents
{
    /** @var Emitter */
    protected $eventEmitter;

    /**
     * Emit an event.
     *
     * @param EventInterface|string $event
     *
     * @return bool true if emitted, false otherwise.
     * @throws \InvalidArgumentException
     *
     */
    protected function emitEvent($event): bool
    {
        if (is_null($this->eventEmitter)) {
            return false;
        }

        $this->validateEvent($event);

        $this->eventEmitter->emit($event);

        return true;
    }

    /**
     * Emit events in batch.
     *
     * @param EventInterface[]|string[] $events
     *
     * @return bool true if all emitted, false otherwise
     * @throws \InvalidArgumentException
     *
     */
    private function emitBatchOfEvents(array $events): bool
    {
        if (is_null($this->eventEmitter)) {
            return false;
        }

        foreach ($events as $e) {
            $this->validateEvent($e);
        }

        $this->emitBatchOfEvents($events);

        return true;
    }

    /**
     * Returns an event emitter.
     *
     * @return EmitterInterface
     */
    public function getEventEmitter(): EmitterInterface
    {
        return $this->eventEmitter;
    }

    /**
     * Set an event emitter.
     *
     * @param Emitter $eventEmitter
     *
     * @return $this
     */
    public function setEventEmitter($eventEmitter)
    {
        $this->eventEmitter = $eventEmitter;

        return $this;
    }

    /**
     * @param $event
     *
     * @return void
     */
    private function validateEvent($event)
    {
        if (!is_string($event) && !$event instanceof EventInterface) {
            throw new \InvalidArgumentException('Event must be either be of type "string" or instance of League\Event\EventInterface');
        }
    }
}
