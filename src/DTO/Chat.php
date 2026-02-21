<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

final class Chat
{
    public function __construct(
        public int     $chatId,
        public string  $type,
        public string  $status,
        public ?string $title,
        public ?int    $participantsCount,
        public ?int    $ownerId,
        public bool    $isPublic,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            chatId:            $d['chat_id'],
            type:              $d['type'],
            status:            $d['status'] ?? 'active',
            title:             $d['title'] ?? null,
            participantsCount: $d['participants_count'] ?? null,
            ownerId:           $d['owner_id'] ?? null,
            isPublic:          $d['is_public'] ?? false,
        );
    }
}
