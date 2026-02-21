<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Resources;

use MaxBotApi\DTO\Member;
use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\ChatMembers;
use PHPUnit\Framework\TestCase;

final class ChatMembersTest extends TestCase
{
    private function mockHttp(): HttpClient
    {
        return $this->createMock(HttpClient::class);
    }

    /** @return array<string, mixed> */
    private function memberData(int $userId = 1, string $name = 'User'): array
    {
        return ['user_id' => $userId, 'name' => $name];
    }

    public function testListReturnsMembers(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/10/members', [], [])
            ->willReturn(['members' => [$this->memberData(1), $this->memberData(2)]]);

        $members = (new ChatMembers($http))->list(10);

        $this->assertCount(2, $members);
        $this->assertContainsOnlyInstancesOf(Member::class, $members);
    }

    public function testListDefaultCountNotPassedInQuery(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/10/members', [], []) // count=20 is default, not passed
            ->willReturn(['members' => []]);

        (new ChatMembers($http))->list(10);
    }

    public function testListWithCustomCountAndMarker(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/10/members', [], ['count' => 5, 'marker' => 100])
            ->willReturn(['members' => []]);

        (new ChatMembers($http))->list(10, 5, 100);
    }

    public function testListReturnsEmptyArrayWhenNoMembers(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['members' => []]);

        $this->assertSame([], (new ChatMembers($http))->list(10));
    }

    public function testAddReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/chats/10/members', ['user_ids' => [1, 2, 3]])
            ->willReturn(['success' => true]);

        $this->assertTrue((new ChatMembers($http))->add(10, [1, 2, 3]));
    }

    public function testRemoveWithoutBlock(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/chats/10/members', ['user_id' => 5])
            ->willReturn(['success' => true]);

        $this->assertTrue((new ChatMembers($http))->remove(10, 5));
    }

    public function testRemoveWithBlockFlagIncluded(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/chats/10/members', ['user_id' => 5, 'block' => true])
            ->willReturn(['success' => true]);

        (new ChatMembers($http))->remove(10, 5, block: true);
    }

    public function testListAdminsReturnsMembers(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/10/members/admins')
            ->willReturn(['admins' => [$this->memberData(99, 'Admin')]]);

        $admins = (new ChatMembers($http))->listAdmins(10);

        $this->assertCount(1, $admins);
        $this->assertInstanceOf(Member::class, $admins[0]);
        $this->assertSame(99, $admins[0]->userId);
    }

    public function testListAdminsReturnsEmptyArrayWhenNoAdmins(): void
    {
        $http = $this->mockHttp();
        $http->method('request')->willReturn(['admins' => []]);

        $this->assertSame([], (new ChatMembers($http))->listAdmins(10));
    }

    public function testAddAdminReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/chats/10/members/admins', ['user_id' => 7])
            ->willReturn(['success' => true]);

        $this->assertTrue((new ChatMembers($http))->addAdmin(10, 7));
    }

    public function testRemoveAdminReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/chats/10/members/admins/7')
            ->willReturn(['success' => true]);

        $this->assertTrue((new ChatMembers($http))->removeAdmin(10, 7));
    }

    public function testGetMeReturnsMember(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/10/members/me')
            ->willReturn($this->memberData(42, 'BotName'));

        $member = (new ChatMembers($http))->getMe(10);

        $this->assertInstanceOf(Member::class, $member);
        $this->assertSame(42, $member->userId);
    }

    public function testLeaveChatReturnsTrueOnSuccess(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('DELETE', '/chats/10/members/me')
            ->willReturn(['success' => true]);

        $this->assertTrue((new ChatMembers($http))->leaveChat(10));
    }
}
