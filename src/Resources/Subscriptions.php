<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\Subscription;
use MaxBotApi\DTO\Update;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;

/**
 * Resource for webhook subscription and long-polling update endpoints.
 */
final class Subscriptions
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Get a list of active webhook subscriptions for this bot.
     *
     * @return Subscription[]
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function list(): array
    {
        $data = $this->http->request('GET', '/subscriptions');

        return array_map(
            static fn(array $s) => Subscription::fromArray($s),
            $data['subscriptions'] ?? [],
        );
    }

    /**
     * Subscribe the bot to updates via webhook.
     * The webhook server must listen on one of the allowed ports: 80, 8080, 443, 8443, or 16384–32383.
     *
     * @param string   $url         Public HTTPS URL of the webhook endpoint.
     * @param string[] $updateTypes Event types to receive; empty means all types.
     * @param string|null $secret   Optional secret verified via the X-Max-Bot-Api-Secret header.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
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
     * Remove the active webhook subscription.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function unsubscribe(): bool
    {
        $data = $this->http->request('DELETE', '/subscriptions');

        return (bool) ($data['success'] ?? false);
    }

    /**
     * Fetch pending updates via long polling.
     * Intended for development only — use webhooks in production.
     *
     * @param int      $limit   Maximum number of updates to return (1–100).
     * @param int      $timeout Long-polling timeout in seconds.
     * @param int|null $marker  Pagination cursor from the previous response.
     * @param string[] $types   Filter updates by event type; empty means all types.
     *
     * @return Update[]
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function getUpdates(
        int   $limit = 100,
        int   $timeout = 30,
        ?int  $marker = null,
        array $types = [],
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
            $data['updates'] ?? [],
        );
    }
}
