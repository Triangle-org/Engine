<?php declare(strict_types=1);

/**
 * @package Triangle Engine
 * @link https://github.com/Triangle-org/Engine
 * @author Ivan Zorin <creator@localzet.com>
 * @copyright Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Tests\Unit;

use Fiber;
use localzet\Server;
use localzet\Server\Events\Fiber as FiberEventLoop;
use localzet\Server\Events\Select;
use PHPUnit\Framework\TestCase;
use Triangle\Engine\Context;

class ContextTest extends TestCase
{
    private ?string $originalEventLoopClass;

    protected function setUp(): void
    {
        parent::setUp();

        Context::init();
        $this->originalEventLoopClass = Server::$eventLoopClass;
    }

    protected function tearDown(): void
    {
        Server::$eventLoopClass = $this->originalEventLoopClass;
        Context::destroy();

        parent::tearDown();
    }

    public function testFiberContextRemainsIsolatedAcrossServerEventLoops(): void
    {
        foreach ([Select::class, FiberEventLoop::class] as $eventLoopClass) {
            Server::$eventLoopClass = $eventLoopClass;

            $first = new Fiber(static function (): string {
                Context::set('requestId', 'first');
                Fiber::suspend();

                return Context::get('requestId');
            });
            $second = new Fiber(static function (): string {
                Context::set('requestId', 'second');
                Fiber::suspend();

                return Context::get('requestId');
            });

            $first->start();
            $second->start();

            $first->resume();
            $second->resume();

            self::assertSame('first', $first->getReturn(), $eventLoopClass);
            self::assertSame('second', $second->getReturn(), $eventLoopClass);
        }
    }
}
