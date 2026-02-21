<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Chat;
use MaxBotApi\DTO\Message;
use MaxBotApi\Http\HttpClient;

final class Chats
{
    public function __construct(private HttpClient $http) {}

    /**
     * Get all chats the bot participates in.
     *
     * @return Chat[]
     */
    public function list(int $count = 50, ?int $marker = null): array
    {
        $query = array_filter([
            'count'  => $count !== 50 ? $count : null,
            'marker' => $marker,
        ], fn($v) => $v !== null);

        $data = $this->http->request('GET', '/chats', [], $query);

        return array_map(
            static fn(array $c) => Chat::fromArray($c),
            $data['chats'] ?? []
        );
    }

    /**
     * Get chat info by ID.
     */
    public function get(int $chatId): Chat
    {
        $data = $this->http->request('GET', "/chats/{$chatId}");

        return Chat::fromArray($data);
    }

    /**
     * Update chat info (title, description, etc.).
     */
    public function update(int $chatId, ?string $title = null, ?string $description = null): Chat
    {
        $body = array_filter([
            'title'       => $title,
            'description' => $description,
        ], fn($v) => $v !== null);

        $data = $this->http->request('PATCH', "/chats/{$chatId}", $body);

        return Chat::fromArray($data);
    }

    /**
     * Delete a chat.
     */
    public function delete(int $chatId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}");

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Send a bot action to a chat (e.g. "typing...").
     * Actions: typing_on, sending_photo, sending_video, sending_audio, sending_file, mark_seen.
     */
    public function sendAction(int $chatId, string $action): bool
    {
        $data = $this->http->request('POST', "/chats/{$chatId}/actions", ['action' => $action]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Get the pinned message in a chat.
     */
    public function getPinnedMessage(int $chatId): ?Message
    {
        $data = $this->http->request('GET', "/chats/{$chatId}/pin");

        if (empty($data)) {
            return null;
        }

        return Message::fromArray($data);
    }

    /**
     * Pin a message in a chat.
     */
    public function pinMessage(int $chatId, string $messageId, bool $notify = true): bool
    {
        $body = ['message_id' => $messageId];

        if (!$notify) {
            $body['notify'] = false;
        }

        $data = $this->http->request('PUT', "/chats/{$chatId}/pin", $body);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Unpin the pinned message in a chat.
     */
    public function unpinMessage(int $chatId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}/pin");

        return (bool) ($data['success'] ?? false);
    }
}
