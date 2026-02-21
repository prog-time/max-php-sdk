<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

final class Member
{
    public function __construct(
        public int     $userId,
        public string  $name,
        public ?string $username,
        public bool    $isBot,
        public bool    $isOwner,
        public bool    $isAdmin,
        public int     $joinTime,
        public int     $lastAccessTime,
        public int     $lastActivityTime,
        /** @var string[]|null */
        public ?array  $permissions,
        public ?string $description,
        public ?string $avatarUrl,
        public ?string $fullAvatarUrl,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            userId:           $d['user_id'],
            name:             $d['name'],
            username:         $d['username'] ?? null,
            isBot:            $d['is_bot'] ?? false,
            isOwner:          $d['is_owner'] ?? false,
            isAdmin:          $d['is_admin'] ?? false,
            joinTime:         $d['join_time'] ?? 0,
            lastAccessTime:   $d['last_access_time'] ?? 0,
            lastActivityTime: $d['last_activity_time'] ?? 0,
            permissions:      $d['permissions'] ?? null,
            description:      $d['description'] ?? null,
            avatarUrl:        $d['avatar_url'] ?? null,
            fullAvatarUrl:    $d['full_avatar_url'] ?? null,
        );
    }
}
