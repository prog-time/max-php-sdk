<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    public function testFromArrayWithNestedStructure(): void
    {
        $data = [
            'body'      => ['mid' => 'msg-1', 'text' => 'Hello'],
            'timestamp' => 1700000000000,
            'sender'    => ['user_id' => 10],
            'recipient' => ['chat_id' => 20, 'user_id' => 30],
        ];

        $msg = Message::fromArray($data);

        $this->assertSame('msg-1', $msg->messageId);
        $this->assertSame(1700000000000, $msg->timestamp);
        $this->assertSame(10, $msg->authorId);
        $this->assertSame(20, $msg->chatId);
        $this->assertSame(30, $msg->userId);
        $this->assertSame('Hello', $msg->text);
    }

    public function testFromArrayWithFlatStructure(): void
    {
        $data = [
            'message_id' => 'msg-flat',
            'timestamp'  => 1600000000000,
            'author_id'  => 5,
            'chat_id'    => 7,
            'user_id'    => 9,
            'text'       => 'Flat text',
        ];

        $msg = Message::fromArray($data);

        $this->assertSame('msg-flat', $msg->messageId);
        $this->assertSame(1600000000000, $msg->timestamp);
        $this->assertSame(5, $msg->authorId);
        $this->assertSame(7, $msg->chatId);
        $this->assertSame(9, $msg->userId);
        $this->assertSame('Flat text', $msg->text);
    }

    public function testFromArrayMissingOptionalFieldsReturnNull(): void
    {
        $msg = Message::fromArray(['body' => ['mid' => 'x'], 'timestamp' => 0]);

        $this->assertSame('x', $msg->messageId);
        $this->assertSame(0, $msg->timestamp);
        $this->assertNull($msg->authorId);
        $this->assertNull($msg->chatId);
        $this->assertNull($msg->userId);
        $this->assertNull($msg->text);
    }

    public function testFromArrayBodyTextTakesPriorityOverRootText(): void
    {
        $data = [
            'body'      => ['mid' => 'm', 'text' => 'body-text'],
            'text'      => 'root-text',
            'timestamp' => 0,
        ];

        $msg = Message::fromArray($data);

        $this->assertSame('body-text', $msg->text);
    }

    public function testFromArraySenderUserIdTakesPriorityOverAuthorId(): void
    {
        $data = [
            'body'      => ['mid' => 'm'],
            'timestamp' => 0,
            'sender'    => ['user_id' => 77],
            'author_id' => 88,
        ];

        $msg = Message::fromArray($data);

        $this->assertSame(77, $msg->authorId);
    }
}
