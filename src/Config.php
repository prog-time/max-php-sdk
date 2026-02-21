<?php

declare(strict_types=1);

namespace MaxBotApi;

/**
 * SDK configuration passed to MaxClient.
 */
final class Config
{
    /**
     * @param string $token   Bot API token used for authentication.
     * @param string $baseUrl Max API base URL.
     * @param int    $timeout HTTP request timeout in seconds.
     */
    public function __construct(
        public readonly string $token,
        public readonly string $baseUrl = 'https://platform-api.max.ru',
        public readonly int    $timeout = 10,
    ) {}
}
