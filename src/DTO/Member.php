<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents a member of a Max chat.
 */
final class Member
{
    /**
     * @param int           $userId           Unique user identifier.
     * @param string        $name             Display name of the user.
     * @param string|null   $username         Username without @, or null if not set.
     * @param bool          $isBot            Whether the member is a bot.
     * @param bool          $isOwner          Whether the member is the chat owner.
     * @param bool          $isAdmin          Whether the member has admin rights.
     * @param int           $joinTime         Unix timestamp in milliseconds when the user joined.
     * @param int           $lastAccessTime   Unix timestamp in milliseconds of last access.
     * @param int           $lastActivityTime Unix timestamp in milliseconds of last activity.
     * @param string[]|null $permissions      Granted admin permissions, or null for regular members.
     * @param string|null   $description      Profile description, or null if not set.
     * @param string|null   $avatarUrl        URL of the avatar thumbnail, or null.
     * @param string|null   $fullAvatarUrl    URL of the full-size avatar, or null.
     */
    public function __construct(
        public readonly int     $userId,
        public readonly string  $name,
        public readonly ?string $username,
        public readonly bool    $isBot,
        public readonly bool    $isOwner,
        public readonly bool    $isAdmin,
        public readonly int     $joinTime,
        public readonly int     $lastAccessTime,
        public readonly int     $lastActivityTime,
        public readonly ?array  $permissions,
        public readonly ?string $description,
        public readonly ?string $avatarUrl,
        public readonly ?string $fullAvatarUrl,
    ) {}

    /**
     * @param array<string, mixed> $d Raw API response data.
     */
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
