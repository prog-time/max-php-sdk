<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

final class Message
{
    public function __construct(
        public string  $messageId,
        public int     $timestamp,
        public ?int    $authorId,
        public ?int    $chatId,
        public ?int    $userId,
        public ?string $text,
    ) {}

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
