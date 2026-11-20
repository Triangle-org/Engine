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

use PHPUnit\Framework\TestCase;
use localzet\Server\Protocols\Http\Response as ServerResponse;
use Triangle\Engine\Config;
use Triangle\Engine\Response;

class Server7HelpersCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        Config::clear();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Config::clear();
        parent::tearDown();
    }

    public function testResponseJsonUsesTheTriangleJsonEncoder(): void
    {
        $response = responseJson(['ok' => true], 422, ['X-Trace' => 'trace']);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(422, $response->getStatusCode());
        self::assertSame('trace', $response->getHeaders()['X-Trace']);
        self::assertSame(['ok' => true], json_decode((string)$response->rawBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testTriangleJsonKeepsTheEngineStringContract(): void
    {
        $encoded = triangle_json(['ok' => true]);

        self::assertIsString($encoded);
        self::assertSame(['ok' => true], json_decode($encoded, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testTriangleResponseKeepsTheEngineEnvelope(): void
    {
        Config::set(['app' => ['debug' => false, 'http_always_200' => false]]);

        $response = triangle_response(['message' => 'invalid'], 422);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(422, $response->getStatusCode());
        self::assertSame(
            ['status' => 422, 'data' => ['message' => 'invalid']],
            json_decode((string)$response->rawBody(), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function testTriangleRedirectReturnsTheEngineResponseAndStripsLineBreaks(): void
    {
        $response = triangle_redirect("/next\r\nX-Injected: value", 302);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/nextX-Injected: value', $response->getHeaders()['Location']);
    }

    public function testServerGlobalHelpersKeepTheirServerResponseContract(): void
    {
        self::assertInstanceOf(ServerResponse::class, response('body'));
        self::assertInstanceOf(ServerResponse::class, json(['ok' => true]));
        self::assertInstanceOf(ServerResponse::class, redirect('/next'));
    }
}
