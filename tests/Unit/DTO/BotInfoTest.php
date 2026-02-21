<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\BotInfo;
use PHPUnit\Framework\TestCase;

final class BotInfoTest extends TestCase
{
    public function testFromArrayWithAllFields(): void
    {
        $data = [
            'user_id'            => 42,
            'name'               => 'MyBot',
            'username'           => 'mybot',
            'is_bot'             => true,
            'last_activity_time' => 1700000000000,
            'description'        => 'A test bot',
            'avatar_url'         => 'https://example.com/avatar.jpg',
            'full_avatar_url'    => 'https://example.com/avatar_full.jpg',
        ];

        $bot = BotInfo::fromArray($data);

        $this->assertSame(42, $bot->userId);
        $this->assertSame('MyBot', $bot->name);
        $this->assertSame('mybot', $bot->username);
        $this->assertTrue($bot->isBot);
        $this->assertSame(1700000000000, $bot->lastActivityTime);
        $this->assertSame('A test bot', $bot->description);
        $this->assertSame('https://example.com/avatar.jpg', $bot->avatarUrl);
        $this->assertSame('https://example.com/avatar_full.jpg', $bot->fullAvatarUrl);
    }

    public function testFromArrayWithMinimalFields(): void
    {
        $bot = BotInfo::fromArray(['user_id' => 1, 'name' => 'Bot']);

        $this->assertSame(1, $bot->userId);
        $this->assertSame('Bot', $bot->name);
        $this->assertNull($bot->username);
        $this->assertTrue($bot->isBot);
        $this->assertSame(0, $bot->lastActivityTime);
        $this->assertNull($bot->description);
        $this->assertNull($bot->avatarUrl);
        $this->assertNull($bot->fullAvatarUrl);
    }

    public function testFromArrayUsesFirstNameFallback(): void
    {
        $bot = BotInfo::fromArray(['user_id' => 1, 'first_name' => 'John']);

        $this->assertSame('John', $bot->name);
    }

    public function testFromArrayNameTakesPriorityOverFirstName(): void
    {
        $bot = BotInfo::fromArray(['user_id' => 1, 'name' => 'Bot', 'first_name' => 'John']);

        $this->assertSame('Bot', $bot->name);
    }

    public function testFromArrayEmptyNameFallback(): void
    {
        $bot = BotInfo::fromArray(['user_id' => 5]);

        $this->assertSame('', $bot->name);
    }
}
