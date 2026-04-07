<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Message;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;

/**
 * Resource for message endpoints.
 */
final class Messages
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Send a text message to a chat or a user.
     * Either $chatId or $userId must be provided.
     *
     * @param string                        $text                Message text.
     * @param int|null                      $chatId              Target chat ID.
     * @param int|null                      $userId              Target user ID (for direct messages).
     * @param string|null                   $format              Text format: 'markdown' or 'html'.
     * @param bool                          $notify              Whether to send a push notification to recipients.
     * @param bool                          $disableLinkPreview  Whether to disable link previews in the message.
     * @param array<int, array<string, mixed>>|null $attachments Media attachments. Each item must have
     *                                                           'type' (image|video|audio|file) and
     *                                                           'payload' with a 'token' key.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function send(
        string  $text,
        ?int    $chatId = null,
        ?int    $userId = null,
        ?string $format = null,
        bool    $notify = true,
        bool    $disableLinkPreview = false,
        ?array  $attachments = null,
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
        if ($attachments !== null) {
            $body['attachments'] = $attachments;
        }

        $data = $this->http->request('POST', '/messages', $body, $query);

        return Message::fromArray($data['message']);
    }

    /**
     * Get messages from a chat.
     * Either $chatId or $messageIds must be provided.
     *
     * @param int|null    $chatId     Chat to load messages from.
     * @param string|null $messageIds Comma-separated list of specific message IDs.
     * @param int|null    $from       Lower bound of the time range (Unix ms).
     * @param int|null    $to         Upper bound of the time range (Unix ms).
     * @param int         $count      Maximum number of messages to return.
     *
     * @return Message[]
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
            $data['messages'] ?? [],
        );
    }

    /**
     * Get a single message by its ID.
     *
     * @param string $messageId Unique message identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function get(string $messageId): Message
    {
        $data = $this->http->request('GET', "/messages/{$messageId}");

        return Message::fromArray($data);
    }

    /**
     * Edit a message (only messages sent within the last 24 hours can be edited).
     *
     * @param string      $messageId   Unique identifier of the message to edit.
     * @param string|null $text        New text content.
     * @param string|null $format      Text format: 'markdown' or 'html'.
     * @param bool        $notify      Whether to send an edit notification to recipients.
     * @param array<int, array<string, mixed>>|null $attachments New attachments. Pass an empty array ([])
     *                                              to remove all existing attachments and inline keyboards.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function edit(
        string  $messageId,
        ?string $text = null,
        ?string $format = null,
        bool    $notify = true,
        ?array  $attachments = null,
    ): Message {
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
        if ($attachments !== null) {
            $body['attachments'] = $attachments;
        }

        $data = $this->http->request('PUT', '/messages', $body, ['message_id' => $messageId]);

        return Message::fromArray($data);
    }

    /**
     * Delete a message (only messages sent within the last 24 hours can be deleted).
     *
     * @param string $messageId Unique identifier of the message to delete.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function delete(string $messageId): bool
    {
        $data = $this->http->request('DELETE', '/messages', [], ['message_id' => $messageId]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Respond to a callback triggered by an inline keyboard button press.
     *
     * @param string                    $callbackId  Callback identifier from the update payload.
     * @param array<string, mixed>|null $message     Optional message to send in response.
     * @param string|null               $notification Toast notification text shown to the user.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
