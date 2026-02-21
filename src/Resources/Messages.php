<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Message;
use MaxBotApi\Http\HttpClient;

final class Messages
{
    public function __construct(private HttpClient $http) {}

    /**
     * Send a message to a chat or a user.
     * Either chat_id or user_id must be provided.
     */
    public function send(
        string  $text,
        ?int    $chatId = null,
        ?int    $userId = null,
        ?string $format = null,
        bool    $notify = true,
        bool    $disableLinkPreview = false,
    ): Message {
        $query = array_filter([
            'chat_id'              => $chatId,
            'user_id'              => $userId,
            'disable_link_preview' => $disableLinkPreview ?: null,
        ], fn($v) => $v !== null);

        $body = ['text' => $text];

        if ($format !== null) {
            $body['format'] = $format;
        }
        if (!$notify) {
            $body['notify'] = false;
        }

        $data = $this->http->request('POST', '/messages', $body, $query);

        return Message::fromArray($data['message']);
    }

    /**
     * Get messages from a chat.
     * Either chat_id or message_ids must be provided.
     *
     * @return Message[]
     */
    public function list(
        ?int    $chatId = null,
        ?string $messageIds = null,
        ?int    $from = null,
        ?int    $to = null,
        int     $count = 50,
    ): array {
        $query = array_filter([
            'chat_id'     => $chatId,
            'message_ids' => $messageIds,
            'from'        => $from,
            'to'          => $to,
            'count'       => $count !== 50 ? $count : null,
        ], fn($v) => $v !== null);

        $data = $this->http->request('GET', '/messages', [], $query);

        return array_map(
            static fn(array $m) => Message::fromArray($m),
            $data['messages'] ?? []
        );
    }

    /**
     * Get a message by its ID.
     */
    public function get(string $messageId): Message
    {
        $data = $this->http->request('GET', "/messages/{$messageId}");

        return Message::fromArray($data);
    }

    /**
     * Edit a message (only messages sent within the last 24 hours can be edited).
     */
    public function edit(
        string  $messageId,
        ?string $text = null,
        ?string $format = null,
        bool    $notify = true,
    ): bool {
        $body = [];

        if ($text !== null) {
            $body['text'] = $text;
        }
        if ($format !== null) {
            $body['format'] = $format;
        }
        if (!$notify) {
            $body['notify'] = false;
        }

        $data = $this->http->request('PUT', '/messages', $body, ['message_id' => $messageId]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Delete a message (only messages sent within the last 24 hours can be deleted).
     */
    public function delete(string $messageId): bool
    {
        $data = $this->http->request('DELETE', '/messages', [], ['message_id' => $messageId]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Answer a callback triggered by an inline button press.
     *
     * @param string|null $notification Toast notification text shown to the user
     */
    public function answerCallback(
        string  $callbackId,
        ?array  $message = null,
        ?string $notification = null,
    ): bool {
        $body = array_filter([
            'message'      => $message,
            'notification' => $notification,
        ], fn($v) => $v !== null);

        $data = $this->http->request('POST', '/answers', $body, ['callback_id' => $callbackId]);

        return (bool) ($data['success'] ?? false);
    }
}
