<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents a Max chat (dialog, group chat, or channel).
 */
final class Chat
{
    /**
     * @param int         $chatId            Unique chat identifier.
     * @param string      $type              Chat type: 'dialog', 'chat', or 'channel'.
     * @param string      $status            Membership status: 'active', 'removed', 'left', 'closed', 'suspended'.
     * @param string|null $title             Chat title (null for dialogs).
     * @param int|null    $participantsCount Number of participants.
     * @param int|null    $ownerId           User ID of the chat owner.
     * @param bool        $isPublic          Whether the chat is publicly accessible.
     */
    public function __construct(
        public readonly int     $chatId,
        public readonly string  $type,
        public readonly string  $status,
        public readonly ?string $title,
        public readonly ?int    $participantsCount,
        public readonly ?int    $ownerId,
        public readonly bool    $isPublic,
    ) {}

    /**
     * @param array<string, mixed> $d Raw API response data.
     */
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
