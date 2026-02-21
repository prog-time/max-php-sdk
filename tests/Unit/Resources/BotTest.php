<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Resources;

use MaxBotApi\DTO\BotInfo;
use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\Bot;
use PHPUnit\Framework\TestCase;

final class BotTest extends TestCase
{
    public function testMeReturnsBotInfo(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/me')
            ->willReturn([
                'user_id'            => 42,
                'name'               => 'MyBot',
                'username'           => 'mybot',
                'is_bot'             => true,
                'last_activity_time' => 1700000000000,
            ]);

        $info = (new Bot($http))->me();

        $this->assertInstanceOf(BotInfo::class, $info);
        $this->assertSame(42, $info->userId);
        $this->assertSame('MyBot', $info->name);
        $this->assertSame('mybot', $info->username);
        $this->assertTrue($info->isBot);
    }
}
