<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\Update;
use PHPUnit\Framework\TestCase;

final class UpdateTest extends TestCase
{
    public function testFromArrayExtractsUpdateTypeAndTimestamp(): void
    {
        $update = Update::fromArray([
            'update_type' => 'message_created',
            'timestamp'   => 1700000000000,
            'message'     => ['body' => ['mid' => 'x', 'text' => 'Hello']],
            'user_locale' => 'ru',
        ]);

        $this->assertSame('message_created', $update->updateType);
        $this->assertSame(1700000000000, $update->timestamp);
    }

    public function testFromArrayExcludesUpdateTypeAndTimestampFromPayload(): void
    {
        $update = Update::fromArray([
            'update_type' => 'bot_started',
            'timestamp'   => 1000,
            'chat_id'     => 55,
            'user'        => ['user_id' => 7],
        ]);

        $this->assertArrayNotHasKey('update_type', $update->payload);
        $this->assertArrayNotHasKey('timestamp', $update->payload);
    }

    public function testFromArrayIncludesEventSpecificFieldsInPayload(): void
    {
        $update = Update::fromArray([
            'update_type' => 'message_created',
            'timestamp'   => 1000,
            'message'     => ['body' => ['mid' => 'm']],
            'user_locale' => 'en',
        ]);

        $this->assertArrayHasKey('message', $update->payload);
        $this->assertArrayHasKey('user_locale', $update->payload);
        $this->assertSame('en', $update->payload['user_locale']);
    }

    public function testFromArrayMessageRemovedPayload(): void
    {
        $update = Update::fromArray([
            'update_type' => 'message_removed',
            'timestamp'   => 2000,
            'message_id'  => 'msg-99',
            'chat_id'     => 10,
            'user_id'     => 5,
        ]);

        $this->assertSame('message_removed', $update->updateType);
        $this->assertSame('msg-99', $update->payload['message_id']);
        $this->assertSame(10, $update->payload['chat_id']);
    }

    public function testFromArrayEmptyPayloadWhenNoExtraFields(): void
    {
        $update = Update::fromArray([
            'update_type' => 'bot_stopped',
            'timestamp'   => 0,
        ]);

        $this->assertSame([], $update->payload);
    }
}
