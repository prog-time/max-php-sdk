<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Resources;

use MaxBotApi\DTO\Subscription;
use MaxBotApi\DTO\Update;
use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\Subscriptions;
use PHPUnit\Framework\TestCase;

final class SubscriptionsTest extends TestCase
{
    private function mockHttp(): HttpClient
    {
        return $this->createMock(HttpClient::class);
    }

    public function testListReturnsSubscriptions(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/subscriptions')
            ->willReturn([
                'subscriptions' => [
                    ['url' => 'https://example.com/hook', 'time' => 1000, 'update_types' => []],
                ],
            ]);

        $subs = (new Subscriptions($http))->list();

        $this->assertCount(1, $subs);
        $this->assertInstanceOf(Subscription::class, $subs[0]);
        $this->assertSame('https://example.com/hook', $subs[0]->url);
    }

    public function testListReturnsEmptyArrayWhenNoSubscriptions(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['subscriptions' => []]);

        $this->assertSame([], (new Subscriptions($http))->list());
    }

    public function testSubscribeMinimal(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', ['url' => 'https://example.com/hook'])
            ->willReturn(['success' => true]);

        $this->assertTrue((new Subscriptions($http))->subscribe('https://example.com/hook'));
    }

    public function testSubscribeWithUpdateTypes(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [
                'url'          => 'https://example.com/hook',
                'update_types' => ['message_created', 'bot_started'],
            ])
            ->willReturn(['success' => true]);

        (new Subscriptions($http))->subscribe('https://example.com/hook', ['message_created', 'bot_started']);
    }

    public function testSubscribeWithSecret(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [
                'url'    => 'https://example.com/hook',
                'secret' => 'my-secret',
            ])
            ->willReturn(['success' => true]);

        (new Subscriptions($http))->subscribe('https://example.com/hook', [], 'my-secret');
    }

    public function testSubscribeWithTypesAndSecret(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [
                'url'          => 'https://example.com/hook',
                'update_types' => ['message_created'],
                'secret'       => 'sec',
            ])
            ->willReturn(['success' => true]);

        (new Subscriptions($http))->subscribe('https://example.com/hook', ['message_created'], 'sec');
    }

    public function testUnsubscribeReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/subscriptions')
            ->willReturn(['success' => true]);

        $this->assertTrue((new Subscriptions($http))->unsubscribe());
    }

    public function testGetUpdatesWithDefaults(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/updates', [], []) // limit=100 and timeout=30 are defaults, not passed
            ->willReturn([
                'updates' => [
                    ['update_type' => 'message_created', 'timestamp' => 1000, 'message' => []],
                ],
            ]);

        $updates = (new Subscriptions($http))->getUpdates();

        $this->assertCount(1, $updates);
        $this->assertInstanceOf(Update::class, $updates[0]);
        $this->assertSame('message_created', $updates[0]->updateType);
    }

    public function testGetUpdatesWithCustomLimit(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/updates', [], ['limit' => 10])
            ->willReturn(['updates' => []]);

        (new Subscriptions($http))->getUpdates(limit: 10);
    }

    public function testGetUpdatesWithCustomTimeout(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/updates', [], ['timeout' => 5])
            ->willReturn(['updates' => []]);

        (new Subscriptions($http))->getUpdates(timeout: 5);
    }

    public function testGetUpdatesWithMarkerAndTypes(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/updates', [], ['marker' => 42, 'types' => 'message_created,bot_started'])
            ->willReturn(['updates' => []]);

        (new Subscriptions($http))->getUpdates(marker: 42, types: ['message_created', 'bot_started']);
    }

    public function testGetUpdatesReturnsEmptyArrayWhenNoUpdates(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['updates' => []]);

        $this->assertSame([], (new Subscriptions($http))->getUpdates());
    }
}
