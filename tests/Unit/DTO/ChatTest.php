<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\Chat;
use PHPUnit\Framework\TestCase;

final class ChatTest extends TestCase
{
    public function testFromArrayWithAllFields(): void
    {
        $data = [
            'chat_id'            => 100,
            'type'               => 'chat',
            'status'             => 'active',
            'title'              => 'Test Group',
            'participants_count' => 5,
            'owner_id'           => 42,
            'is_public'          => true,
        ];

        $chat = Chat::fromArray($data);

        $this->assertSame(100, $chat->chatId);
        $this->assertSame('chat', $chat->type);
        $this->assertSame('active', $chat->status);
        $this->assertSame('Test Group', $chat->title);
        $this->assertSame(5, $chat->participantsCount);
        $this->assertSame(42, $chat->ownerId);
        $this->assertFalse($chat->isPublic);
    }

    public function testFromArrayDefaultValues(): void
    {
        $chat = Chat::fromArray(['chat_id' => 1, 'type' => 'dialog']);

        $this->assertSame('active', $chat->status);
        $this->assertNull($chat->title);
        $this->assertNull($chat->participantsCount);
        $this->assertNull($chat->ownerId);
        $this->assertFalse($chat->isPublic);
    }

    public function testFromArrayChannelType(): void
    {
        $chat = Chat::fromArray([
            'chat_id' => 200,
            'type'    => 'channel',
            'status'  => 'active',
            'title'   => 'News',
            'is_public' => true,
        ]);

        $this->assertSame('channel', $chat->type);
        $this->assertSame('News', $chat->title);
    }

    public function testFromArrayRemovedStatus(): void
    {
        $chat = Chat::fromArray([
            'chat_id' => 1,
            'type'    => 'chat',
            'status'  => 'removed',
        ]);

        $this->assertSame('removed', $chat->status);
    }
}
