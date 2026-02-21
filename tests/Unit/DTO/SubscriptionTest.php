<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\Subscription;
use PHPUnit\Framework\TestCase;

final class SubscriptionTest extends TestCase
{
    public function testFromArrayWithUpdateTypes(): void
    {
        $sub = Subscription::fromArray([
            'url'          => 'https://example.com/hook',
            'time'         => 1700000000000,
            'update_types' => ['message_created', 'bot_started'],
        ]);

        $this->assertSame('https://example.com/hook', $sub->url);
        $this->assertSame(1700000000000, $sub->time);
        $this->assertSame(['message_created', 'bot_started'], $sub->updateTypes);
    }

    public function testFromArrayWithoutUpdateTypesDefaultsToEmptyArray(): void
    {
        $sub = Subscription::fromArray([
            'url'  => 'https://example.com/hook',
            'time' => 1000,
        ]);

        $this->assertSame([], $sub->updateTypes);
    }

    public function testFromArrayPreservesUrl(): void
    {
        $url = 'https://my-bot.example.com/webhook?token=abc';
        $sub = Subscription::fromArray(['url' => $url, 'time' => 0]);

        $this->assertSame($url, $sub->url);
    }
}
