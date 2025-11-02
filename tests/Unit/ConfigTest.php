<?php

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

