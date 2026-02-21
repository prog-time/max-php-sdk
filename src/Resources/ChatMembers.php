<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Member;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;

/**
 * Resource for chat member endpoints.
 */
final class ChatMembers
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Get a paginated list of chat members.
     *
     * @param int      $chatId Unique chat identifier.
     * @param int      $count  Maximum number of members to return.
     * @param int|null $marker Pagination cursor from the previous response.
     *
     * @return Member[]
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function list(int $chatId, int $count = 20, ?int $marker = null): array
    {
        $query = array_filter([
            'count'  => $count !== 20 ? $count : null,
            'marker' => $marker,
        ], fn($v) => $v !== null);

        $data = $this->http->request('GET', "/chats/{$chatId}/members", [], $query);

        return array_map(
            static fn(array $m) => Member::fromArray($m),
            $data['members'] ?? [],
        );
    }

    /**
     * Add users to a chat.
     *
     * @param int   $chatId  Unique chat identifier.
     * @param int[] $userIds List of user IDs to add.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function add(int $chatId, array $userIds): bool
    {
        $data = $this->http->request('POST', "/chats/{$chatId}/members", [
            'user_ids' => $userIds,
        ]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Remove a member from a chat.
     *
     * @param int  $chatId Unique chat identifier.
     * @param int  $userId User ID to remove.
     * @param bool $block  If true, also block the user from rejoining.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function remove(int $chatId, int $userId, bool $block = false): bool
    {
        $body = ['user_id' => $userId];

        if ($block) {
            $body['block'] = true;
        }

        $data = $this->http->request('DELETE', "/chats/{$chatId}/members", $body);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Get a list of chat administrators.
     *
     * @param int $chatId Unique chat identifier.
     *
     * @return Member[]
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function listAdmins(int $chatId): array
    {
        $data = $this->http->request('GET', "/chats/{$chatId}/members/admins");

        return array_map(
            static fn(array $m) => Member::fromArray($m),
            $data['admins'] ?? [],
        );
    }

    /**
     * Grant admin rights to a chat member.
     *
     * @param int $chatId Unique chat identifier.
     * @param int $userId User ID to promote.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function addAdmin(int $chatId, int $userId): bool
    {
        $data = $this->http->request('POST', "/chats/{$chatId}/members/admins", [
            'user_id' => $userId,
        ]);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Revoke admin rights from a chat member.
     *
     * @param int $chatId Unique chat identifier.
     * @param int $userId User ID to demote.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function removeAdmin(int $chatId, int $userId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}/members/admins/{$userId}");

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Get the bot's own membership details in a chat.
     *
     * @param int $chatId Unique chat identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function getMe(int $chatId): Member
    {
        $data = $this->http->request('GET', "/chats/{$chatId}/members/me");

        return Member::fromArray($data);
    }

    /**
     * Remove the bot from a chat (leave).
     *
     * @param int $chatId Unique chat identifier.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function leaveChat(int $chatId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}/members/me");

        return (bool) ($data['success'] ?? false);
    }
}
