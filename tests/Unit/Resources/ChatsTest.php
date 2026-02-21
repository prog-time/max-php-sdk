<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Resources;

use MaxBotApi\DTO\Chat;
use MaxBotApi\DTO\Message;
use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\Chats;
use PHPUnit\Framework\TestCase;

final class ChatsTest extends TestCase
{
    private function mockHttp(): HttpClient
    {
        return $this->createMock(HttpClient::class);
    }

    /** @return array<string, mixed> */
    private function chatData(int $chatId = 1, string $type = 'chat'): array
    {
        return ['chat_id' => $chatId, 'type' => $type, 'status' => 'active'];
    }

    public function testListReturnsChats(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats', [], [])
            ->willReturn(['chats' => [$this->chatData(1), $this->chatData(2)]]);

        $chats = (new Chats($http))->list();

        $this->assertCount(2, $chats);
        $this->assertContainsOnlyInstancesOf(Chat::class, $chats);
    }

    public function testListDefaultCountNotPassedInQuery(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats', [], []) // count=50 is default, not passed
            ->willReturn(['chats' => []]);

        (new Chats($http))->list();
    }

    public function testListWithCustomCountAndMarker(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats', [], ['count' => 10, 'marker' => 500])
            ->willReturn(['chats' => []]);

        (new Chats($http))->list(10, 500);
    }

    public function testListReturnsEmptyArrayWhenNoChats(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['chats' => []]);

        $this->assertSame([], (new Chats($http))->list());
    }

    public function testGetReturnsChat(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/42')
            ->willReturn($this->chatData(42));

        $chat = (new Chats($http))->get(42);

        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertSame(42, $chat->chatId);
    }

    public function testUpdateWithTitle(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PATCH', '/chats/10', ['title' => 'New Title'])
            ->willReturn(array_merge($this->chatData(10), ['title' => 'New Title']));

        $chat = (new Chats($http))->update(10, title: 'New Title');

        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertSame('New Title', $chat->title);
    }

    public function testUpdateWithDescription(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PATCH', '/chats/10', ['description' => 'A group chat'])
            ->willReturn($this->chatData(10));

        (new Chats($http))->update(10, description: 'A group chat');
    }

    public function testUpdateWithTitleAndDescription(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PATCH', '/chats/10', ['title' => 'T', 'description' => 'D'])
            ->willReturn($this->chatData(10));

        (new Chats($http))->update(10, title: 'T', description: 'D');
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/chats/5')
            ->willReturn(['success' => true]);

        $this->assertTrue((new Chats($http))->delete(5));
    }

    public function testDeleteReturnsFalseOnFailure(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['success' => false]);

        $this->assertFalse((new Chats($http))->delete(5));
    }

    public function testSendAction(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/chats/3/actions', ['action' => 'typing_on'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Chats($http))->sendAction(3, 'typing_on'));
    }

    public function testGetPinnedMessageReturnsMessage(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/1/pin')
            ->willReturn(['body' => ['mid' => 'pinned-msg', 'text' => 'Pinned'], 'timestamp' => 0]);

        $msg = (new Chats($http))->getPinnedMessage(1);

        $this->assertInstanceOf(Message::class, $msg);
        $this->assertSame('pinned-msg', $msg->messageId);
    }

    public function testGetPinnedMessageReturnsNullWhenEmpty(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/1/pin')
            ->willReturn([]);

        $this->assertNull((new Chats($http))->getPinnedMessage(1));
    }

    public function testPinMessage(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PUT', '/chats/1/pin', ['message_id' => 'msg-1'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Chats($http))->pinMessage(1, 'msg-1'));
    }

    public function testPinMessageWithoutNotify(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PUT', '/chats/1/pin', ['message_id' => 'msg-1', 'notify' => false])
            ->willReturn(['success' => true]);

        (new Chats($http))->pinMessage(1, 'msg-1', notify: false);
    }

    public function testUnpinMessage(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/chats/1/pin')
            ->willReturn(['success' => true]);

        $this->assertTrue((new Chats($http))->unpinMessage(1));
    }
}
