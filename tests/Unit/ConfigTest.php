<?php

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
use Triangle\Engine\Config;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Config::clear();
    }

    public function testConfigSetAndGet(): void
    {
        Config::set(['test' => ['key' => 'value']]);
        
        $this->assertEquals('value', Config::get('test.key'));
    }

    public function testConfigGetWithDefault(): void
    {
        $value = Config::get('non.existent.key', 'default');
        
        $this->assertEquals('default', $value);
    }

    public function testConfigGetAll(): void
    {
        Config::set(['app' => ['name' => 'Test']]);
        
        $all = Config::get();
        
        $this->assertIsArray($all);
        $this->assertArrayHasKey('app', $all);
    }

    public function testConfigClear(): void
    {
        Config::set(['test' => 'value']);
        Config::clear();
        
        $value = Config::get('test');
        $this->assertNull($value);
    }
}

