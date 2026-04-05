<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Resources;

use MaxBotApi\DTO\Message;
use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\Messages;
use PHPUnit\Framework\TestCase;

final class MessagesTest extends TestCase
{
    private function mockHttp(): HttpClient
    {
        return $this->createMock(HttpClient::class);
    }

    /** @return array<string, mixed> */
    private function messageResponse(string $mid = 'msg-1', string $text = 'Hello'): array
    {
        return [
            'message' => [
                'body'      => ['mid' => $mid, 'text' => $text],
                'timestamp' => 1700000000000,
                'sender'    => ['user_id' => 10],
                'recipient' => ['chat_id' => 20],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // send()
    // -------------------------------------------------------------------------

    public function testSendWithChatId(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', ['text' => 'Hello'], ['chat_id' => 55])
            ->willReturn($this->messageResponse());

        $result = (new Messages($http))->send('Hello', chatId: 55);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertSame('msg-1', $result->messageId);
    }

    public function testSendWithUserId(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', ['text' => 'Hi'], ['user_id' => 7])
            ->willReturn($this->messageResponse('msg-2', 'Hi'));

        $result = (new Messages($http))->send('Hi', userId: 7);

        $this->assertInstanceOf(Message::class, $result);
    }

    public function testSendWithMarkdownFormat(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', ['text' => '**bold**', 'format' => 'markdown'], ['chat_id' => 1])
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('**bold**', chatId: 1, format: 'markdown');
    }

    public function testSendWithNotifyFalse(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', ['text' => 'Silent', 'notify' => false], ['chat_id' => 1])
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('Silent', chatId: 1, notify: false);
    }

    public function testSendWithDisableLinkPreview(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', ['text' => 'No preview'], ['chat_id' => 1, 'disable_link_preview' => true])
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('No preview', chatId: 1, disableLinkPreview: true);
    }

    public function testSendWithSingleImageAttachment(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'image', 'payload' => ['token' => 'img-token-abc']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                ['text' => 'Here is your image!', 'attachments' => $attachments],
                ['chat_id' => 55],
            )
            ->willReturn($this->messageResponse());

        $result = (new Messages($http))->send('Here is your image!', chatId: 55, attachments: $attachments);

        $this->assertInstanceOf(Message::class, $result);
    }

    public function testSendWithVideoAttachment(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'video', 'payload' => ['token' => 'vid-token-xyz']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                ['text' => 'Watch this!', 'attachments' => $attachments],
                ['chat_id' => 1],
            )
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('Watch this!', chatId: 1, attachments: $attachments);
    }

    public function testSendWithAudioAttachment(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'audio', 'payload' => ['token' => 'aud-token-xyz']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                ['text' => 'Listen!', 'attachments' => $attachments],
                ['chat_id' => 1],
            )
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('Listen!', chatId: 1, attachments: $attachments);
    }

    public function testSendWithFileAttachment(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'file', 'payload' => ['token' => 'file-token-xyz']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                ['text' => 'Here is the doc.', 'attachments' => $attachments],
                ['chat_id' => 1],
            )
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('Here is the doc.', chatId: 1, attachments: $attachments);
    }

    public function testSendWithMultipleAttachments(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'image', 'payload' => ['token' => 'tok-1']],
            ['type' => 'image', 'payload' => ['token' => 'tok-2']],
            ['type' => 'image', 'payload' => ['token' => 'tok-3']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                ['text' => 'Photos!', 'attachments' => $attachments],
                ['chat_id' => 1],
            )
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('Photos!', chatId: 1, attachments: $attachments);
    }

    public function testSendNullAttachmentsNotIncludedInBody(): void
    {
        $http = $this->mockHttp();

        // attachments key must NOT be present in the body when null
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', ['text' => 'Hello'], ['chat_id' => 1])
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('Hello', chatId: 1, attachments: null);
    }

    public function testSendWithAttachmentsAndFormat(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'image', 'payload' => ['token' => 'img-tok']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                ['text' => '**caption**', 'format' => 'markdown', 'attachments' => $attachments],
                ['chat_id' => 1],
            )
            ->willReturn($this->messageResponse());

        (new Messages($http))->send('**caption**', chatId: 1, format: 'markdown', attachments: $attachments);
    }

    // -------------------------------------------------------------------------
    // list()
    // -------------------------------------------------------------------------

    public function testListReturnsMappedMessages(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/messages', [], ['chat_id' => 10])
            ->willReturn([
                'messages' => [
                    ['body' => ['mid' => 'a', 'text' => 'A'], 'timestamp' => 100],
                    ['body' => ['mid' => 'b', 'text' => 'B'], 'timestamp' => 200],
                ],
            ]);

        $messages = (new Messages($http))->list(chatId: 10);

        $this->assertCount(2, $messages);
        $this->assertContainsOnlyInstancesOf(Message::class, $messages);
        $this->assertSame('a', $messages[0]->messageId);
        $this->assertSame('b', $messages[1]->messageId);
    }

    public function testListWithCustomCountPassedInQuery(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/messages', [], ['chat_id' => 1, 'count' => 10])
            ->willReturn(['messages' => []]);

        (new Messages($http))->list(chatId: 1, count: 10);
    }

    public function testListDefaultCountNotIncludedInQuery(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/messages', [], ['chat_id' => 1])
            ->willReturn(['messages' => []]);

        (new Messages($http))->list(chatId: 1); // count defaults to 50
    }

    public function testListReturnsEmptyArrayWhenNoMessages(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['messages' => []]);

        $this->assertSame([], (new Messages($http))->list(chatId: 1));
    }

    // -------------------------------------------------------------------------
    // get()
    // -------------------------------------------------------------------------

    public function testGetSingleMessage(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/messages/msg-99')
            ->willReturn(['body' => ['mid' => 'msg-99', 'text' => 'test'], 'timestamp' => 0]);

        $msg = (new Messages($http))->get('msg-99');

        $this->assertInstanceOf(Message::class, $msg);
        $this->assertSame('msg-99', $msg->messageId);
    }

    // -------------------------------------------------------------------------
    // edit()
    // -------------------------------------------------------------------------

    public function testEditReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PUT', '/messages', ['text' => 'Edited'], ['message_id' => 'msg-1'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Messages($http))->edit('msg-1', 'Edited'));
    }

    public function testEditReturnsFalseWhenSuccessIsMissing(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn([]);

        $this->assertFalse((new Messages($http))->edit('msg-1', 'Edited'));
    }

    public function testEditWithFormatAndNotifyFalse(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('PUT', '/messages', ['text' => 'Upd', 'format' => 'html', 'notify' => false], ['message_id' => 'm'])
            ->willReturn(['success' => true]);

        (new Messages($http))->edit('m', 'Upd', format: 'html', notify: false);
    }

    public function testEditWithNullAttachmentsDoesNotIncludeKeyInBody(): void
    {
        $http = $this->mockHttp();
        // 'attachments' must NOT appear in the body when null (backward compat)
        $http->expects($this->once())
            ->method('request')
            ->with('PUT', '/messages', ['text' => 'Hello'], ['message_id' => 'msg-1'])
            ->willReturn(['success' => true]);

        (new Messages($http))->edit('msg-1', 'Hello', attachments: null);
    }

    public function testEditWithEmptyAttachmentsSendsEmptyArrayToRemoveKeyboard(): void
    {
        $http = $this->mockHttp();
        // Empty array must be sent explicitly so the API removes attachments/keyboard
        $http->expects($this->once())
            ->method('request')
            ->with('PUT', '/messages', ['text' => 'Меню закрыто.', 'attachments' => []], ['message_id' => 'msg-1'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Messages($http))->edit('msg-1', 'Меню закрыто.', attachments: []));
    }

    public function testEditWithAttachmentsReplacesExisting(): void
    {
        $http = $this->mockHttp();

        $attachments = [
            ['type' => 'image', 'payload' => ['token' => 'new-img-tok']],
        ];

        $http->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                '/messages',
                ['text' => 'Updated', 'attachments' => $attachments],
                ['message_id' => 'msg-5'],
            )
            ->willReturn(['success' => true]);

        $this->assertTrue((new Messages($http))->edit('msg-5', 'Updated', attachments: $attachments));
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/messages', [], ['message_id' => 'msg-1'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Messages($http))->delete('msg-1'));
    }

    // -------------------------------------------------------------------------
    // answerCallback()
    // -------------------------------------------------------------------------

    public function testAnswerCallbackMinimal(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/answers', [], ['callback_id' => 'cb-1'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Messages($http))->answerCallback('cb-1'));
    }

    public function testAnswerCallbackWithNotification(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/answers', ['notification' => 'Done!'], ['callback_id' => 'cb-2'])
            ->willReturn(['success' => true]);

        (new Messages($http))->answerCallback('cb-2', notification: 'Done!');
    }

    public function testAnswerCallbackWithMessageAndNotification(): void
    {
        $http = $this->mockHttp();
        $msgPayload = ['text' => 'Reply'];
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/answers', ['message' => $msgPayload, 'notification' => 'OK'], ['callback_id' => 'cb-3'])
            ->willReturn(['success' => true]);

        (new Messages($http))->answerCallback('cb-3', message: $msgPayload, notification: 'OK');
    }
}
