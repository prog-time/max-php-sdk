<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit;

use MaxBotApi\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new Config('my-token');

        $this->assertSame('my-token', $config->token);
        $this->assertSame('https://platform-api.max.ru', $config->baseUrl);
        $this->assertSame(10, $config->timeout);
    }

    public function testCustomBaseUrl(): void
    {
        $config = new Config('tok', 'https://api.example.com');

        $this->assertSame('tok', $config->token);
        $this->assertSame('https://api.example.com', $config->baseUrl);
        $this->assertSame(10, $config->timeout);
    }

    public function testCustomTimeout(): void
    {
        $config = new Config('tok', 'https://platform-api.max.ru', 30);

        $this->assertSame(30, $config->timeout);
    }

    public function testAllCustomValues(): void
    {
        $config = new Config('secret-token', 'https://custom.api.ru', 60);

        $this->assertSame('secret-token', $config->token);
        $this->assertSame('https://custom.api.ru', $config->baseUrl);
        $this->assertSame(60, $config->timeout);
    }
}
