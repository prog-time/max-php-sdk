<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

final class BotInfo
{
    public function __construct(
        public int     $userId,
        public string  $name,
        public ?string $username,
        public bool    $isBot,
        public int     $lastActivityTime,
        public ?string $description,
        public ?string $avatarUrl,
        public ?string $fullAvatarUrl,
    ) {}

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
