<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Chat;
use MaxBotApi\DTO\Message;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;

/**
 * Resource for chat endpoints.
 */
final class Chats
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Get all chats the bot participates in.
     *
     * @param int      $count  Maximum number of chats to return.
     * @param int|null $marker Pagination cursor from the previous response.
     *
     * @return Chat[]
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
            $data['chats'] ?? [],
        );
    }

    /**
     * Get chat information by ID.
     *
     * @param int $chatId Unique chat identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function get(int $chatId): Chat
    {
        $data = $this->http->request('GET', "/chats/{$chatId}");

        return Chat::fromArray($data);
    }

    /**
     * Update chat metadata such as title or description.
     *
     * @param int         $chatId      Unique chat identifier.
     * @param string|null $title       New chat title.
     * @param string|null $description New chat description.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
     *
     * @param int $chatId Unique chat identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function delete(int $chatId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}");

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Send a typing or media indicator action to a chat.
     *
     * @param int    $chatId Unique chat identifier.
     * @param string $action One of: typing_on, sending_photo, sending_video,
     *                       sending_audio, sending_file, mark_seen.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function sendAction(int $chatId, string $action): bool
    {
        $data = $this->http->request('POST', "/chats/{$chatId}/actions", ['action' => $action]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Get the currently pinned message in a chat.
     *
     * @param int $chatId Unique chat identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
     *
     * @param int    $chatId    Unique chat identifier.
     * @param string $messageId Unique identifier of the message to pin.
     * @param bool   $notify    Whether to notify chat members about the pinned message.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
     * Unpin the currently pinned message in a chat.
     *
     * @param int $chatId Unique chat identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function unpinMessage(int $chatId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}/pin");

        return (bool) ($data['success'] ?? false);
    }
}
