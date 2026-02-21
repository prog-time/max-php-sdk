<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\BotInfo;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;

/**
 * Resource for bot profile endpoints.
 */
final class Bot
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Get the authenticated bot's profile information.
     *
     * @return BotInfo
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function me(): BotInfo
    {
        $data = $this->http->request('GET', '/me');

        return BotInfo::fromArray($data);
    }
}
