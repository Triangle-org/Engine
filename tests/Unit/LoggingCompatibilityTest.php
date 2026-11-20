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

use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use support\Log;
use Triangle\Engine\Config;
use Triangle\Engine\Log\MongoDBHandler;

class LoggingCompatibilityTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::clear();

        parent::tearDown();
    }

    public function testSupportLoggerWritesThroughMonologThree(): void
    {
        $channel = 'engine-test-' . bin2hex(random_bytes(4));
        Config::set([
            'log' => [
                $channel => [
                    'handlers' => [[
                        'class' => TestHandler::class,
                        'constructor' => [],
                        'formatter' => [],
                    ]],
                ],
            ],
        ]);

        $logger = Log::channel($channel);
        $logger->info('engine logger record');

        $handler = $logger->getHandlers()[0];
        self::assertInstanceOf(TestHandler::class, $handler);
        self::assertTrue($handler->hasInfoThatContains('engine logger record'));
    }

    public function testMongoDbHandlerAcceptsMonologThreeRecords(): void
    {
        $method = new ReflectionMethod(MongoDBHandler::class, 'write');
        $recordType = $method->getParameters()[0]->getType();

        self::assertSame(LogRecord::class, $recordType->getName());
    }
}
