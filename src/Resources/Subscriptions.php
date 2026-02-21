<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Subscription;
use MaxBotApi\DTO\Update;
use MaxBotApi\Http\HttpClient;

final class Subscriptions
{
    public function __construct(private HttpClient $http) {}

    /**
     * Get a list of active webhook subscriptions.
     *
     * @return Subscription[]
     */
    public function list(): array
    {
        $data = $this->http->request('GET', '/subscriptions');

        return array_map(
            static fn(array $s) => Subscription::fromArray($s),
            $data['subscriptions'] ?? []
        );
    }

    /**
     * Subscribe to updates via webhook.
     * The server must listen on ports: 80, 8080, 443, 8443 or 16384–32383.
     *
     * @param string[] $updateTypes Event types to receive: message_created, bot_started, etc.
     */
    public function subscribe(string $url, array $updateTypes = [], ?string $secret = null): bool
    {
        $body = ['url' => $url];

        if (!empty($updateTypes)) {
            $body['update_types'] = $updateTypes;
        }
        if ($secret !== null) {
            $body['secret'] = $secret;
        }

        $data = $this->http->request('POST', '/subscriptions', $body);

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Unsubscribe from webhook.
     */
    public function unsubscribe(): bool
    {
        $data = $this->http->request('DELETE', '/subscriptions');

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Fetch updates via long polling (development only, use webhooks in production).
     *
     * @param string[] $types Filter by update types
     * @return Update[]
     */
    public function getUpdates(
        int     $limit = 100,
        int     $timeout = 30,
        ?int    $marker = null,
        array   $types = [],
    ): array {
        $query = array_filter([
            'limit'   => $limit !== 100 ? $limit : null,
            'timeout' => $timeout !== 30 ? $timeout : null,
            'marker'  => $marker,
            'types'   => !empty($types) ? implode(',', $types) : null,
        ], fn($v) => $v !== null);

        $data = $this->http->request('GET', '/updates', [], $query);

        return array_map(
            static fn(array $u) => Update::fromArray($u),
            $data['updates'] ?? []
        );
    }
}
