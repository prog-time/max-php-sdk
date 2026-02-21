<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents the authenticated bot's profile information.
 */
final class BotInfo
{
    /**
     * @param int         $userId           Unique user identifier of the bot.
     * @param string      $name             Display name of the bot.
     * @param string|null $username         Bot username (without @), or null if not set.
     * @param bool        $isBot            Always true for bots.
     * @param int         $lastActivityTime Unix timestamp in milliseconds of last activity.
     * @param string|null $description      Bot description, or null if not set.
     * @param string|null $avatarUrl        URL of the bot's avatar thumbnail, or null.
     * @param string|null $fullAvatarUrl    URL of the bot's full-size avatar, or null.
     */
    public function __construct(
        public readonly int     $userId,
        public readonly string  $name,
        public readonly ?string $username,
        public readonly bool    $isBot,
        public readonly int     $lastActivityTime,
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
            name:             $d['name'] ?? $d['first_name'] ?? '',
            username:         $d['username'] ?? null,
            isBot:            $d['is_bot'] ?? true,
            lastActivityTime: $d['last_activity_time'] ?? 0,
            description:      $d['description'] ?? null,
            avatarUrl:        $d['avatar_url'] ?? null,
            fullAvatarUrl:    $d['full_avatar_url'] ?? null,
        );
    }
}
