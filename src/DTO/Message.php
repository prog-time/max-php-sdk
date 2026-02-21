<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents a message received from or sent to the Max API.
 */
final class Message
{
    /**
     * @param string      $messageId Unique message identifier (body.mid).
     * @param int         $timestamp Unix timestamp in milliseconds.
     * @param int|null    $authorId  User ID of the message sender.
     * @param int|null    $chatId    Chat ID the message belongs to.
     * @param int|null    $userId    Recipient user ID (for dialog messages).
     * @param string|null $text      Plain text content of the message.
     */
    public function __construct(
        public readonly string  $messageId,
        public readonly int     $timestamp,
        public readonly ?int    $authorId,
        public readonly ?int    $chatId,
        public readonly ?int    $userId,
        public readonly ?string $text,
    ) {}

    /**
     * @param array<string, mixed> $d Raw API response data.
     */
    public static function fromArray(array $d): self
    {
        $body      = $d['body'] ?? $d;
        $recipient = $d['recipient'] ?? [];
        $sender    = $d['sender'] ?? [];

        return new self(
            messageId: $body['mid'] ?? $d['message_id'] ?? '',
            timestamp: $d['timestamp'] ?? 0,
            authorId:  $sender['user_id'] ?? $d['author_id'] ?? null,
            chatId:    $recipient['chat_id'] ?? $d['chat_id'] ?? null,
            userId:    $recipient['user_id'] ?? $d['user_id'] ?? null,
            text:      $body['text'] ?? $d['text'] ?? null,
        );
    }
}
