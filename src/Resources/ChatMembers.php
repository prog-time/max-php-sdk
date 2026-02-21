<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Member;
use MaxBotApi\Http\HttpClient;

final class ChatMembers
{
    public function __construct(private HttpClient $http) {}

    /**
     * Get a list of chat members.
     *
     * @return Member[]
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
            $data['members'] ?? []
        );
    }

    /**
     * Add members to a chat.
     *
     * @param int[] $userIds
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
     * If block is true, the user will also be blocked from rejoining.
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
     * @return Member[]
     */
    public function listAdmins(int $chatId): array
    {
        $data = $this->http->request('GET', "/chats/{$chatId}/members/admins");

        return array_map(
            static fn(array $m) => Member::fromArray($m),
            $data['admins'] ?? []
        );
    }

    /**
     * Grant admin rights to a chat member.
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
     */
    public function removeAdmin(int $chatId, int $userId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}/members/admins/{$userId}");

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Get the bot's own membership status in a chat.
     */
    public function getMe(int $chatId): Member
    {
        $data = $this->http->request('GET', "/chats/{$chatId}/members/me");

        return Member::fromArray($data);
    }

    /**
     * Remove the bot from a chat.
     */
    public function leaveChat(int $chatId): bool
    {
        $data = $this->http->request('DELETE', "/chats/{$chatId}/members/me");

        return (bool) ($data['success'] ?? false);
    }
}
