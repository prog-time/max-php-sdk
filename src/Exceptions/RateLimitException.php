<?php

namespace MaxBotApi\Exceptions;

class RateLimitException extends ApiException
{
    public function __construct(
        string $message = 'Rate limit exceeded',
        public ?int $retryAfter = null
    ) {
        parent::__construct($message);
    }
}
