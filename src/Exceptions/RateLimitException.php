<?php

declare(strict_types=1);

namespace MaxBotApi\Exceptions;

/**
 * Thrown when the Max API returns HTTP 429 Too Many Requests.
 */
class RateLimitException extends ApiException
{
    /**
     * @param string   $message    Human-readable error message.
     * @param int|null $retryAfter Seconds to wait before retrying, parsed from the Retry-After header.
     */
    public function __construct(
        string $message = 'Rate limit exceeded',
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }
}
