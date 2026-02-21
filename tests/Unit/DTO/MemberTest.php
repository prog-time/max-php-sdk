<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\Member;
use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function testFromArrayWithAllFields(): void
    {
        $data = [
            'user_id'            => 99,
            'name'               => 'Alice',
            'username'           => 'alice',
            'is_bot'             => false,
            'is_owner'           => true,
            'is_admin'           => true,
            'join_time'          => 1000,
            'last_access_time'   => 2000,
            'last_activity_time' => 3000,
            'permissions'        => ['write', 'read'],
            'description'        => 'Admin user',
            'avatar_url'         => 'https://example.com/alice.jpg',
            'full_avatar_url'    => 'https://example.com/alice_full.jpg',
        ];

        $member = Member::fromArray($data);

        $this->assertSame(99, $member->userId);
        $this->assertSame('Alice', $member->name);
        $this->assertSame('alice', $member->username);
        $this->assertFalse($member->isBot);
        $this->assertTrue($member->isOwner);
        $this->assertTrue($member->isAdmin);
        $this->assertSame(1000, $member->joinTime);
        $this->assertSame(2000, $member->lastAccessTime);
        $this->assertSame(3000, $member->lastActivityTime);
        $this->assertSame(['write', 'read'], $member->permissions);
        $this->assertSame('Admin user', $member->description);
        $this->assertSame('https://example.com/alice.jpg', $member->avatarUrl);
        $this->assertSame('https://example.com/alice_full.jpg', $member->fullAvatarUrl);
    }

    public function testFromArrayDefaultValues(): void
    {
        $member = Member::fromArray(['user_id' => 1, 'name' => 'Bob']);

        $this->assertFalse($member->isBot);
        $this->assertFalse($member->isOwner);
        $this->assertFalse($member->isAdmin);
        $this->assertSame(0, $member->joinTime);
        $this->assertSame(0, $member->lastAccessTime);
        $this->assertSame(0, $member->lastActivityTime);
        $this->assertNull($member->permissions);
        $this->assertNull($member->username);
        $this->assertNull($member->description);
        $this->assertNull($member->avatarUrl);
        $this->assertNull($member->fullAvatarUrl);
    }

    public function testFromArrayBotFlag(): void
    {
        $member = Member::fromArray(['user_id' => 5, 'name' => 'Helper', 'is_bot' => true]);

        $this->assertTrue($member->isBot);
    }
}
