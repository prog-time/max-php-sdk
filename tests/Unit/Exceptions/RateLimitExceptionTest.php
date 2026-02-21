<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Exceptions;

use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\RateLimitException;
use PHPUnit\Framework\TestCase;

final class RateLimitExceptionTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $e = new RateLimitException();

        $this->assertSame('Rate limit exceeded', $e->getMessage());
    }

    public function testRetryAfterIsNullByDefault(): void
    {
        $e = new RateLimitException();

        $this->assertNull($e->retryAfter);
    }

    public function testWithRetryAfterValue(): void
    {
        $e = new RateLimitException('Rate limit exceeded', 30);

        $this->assertSame(30, $e->retryAfter);
    }

    public function testExtendsApiException(): void
    {
        $this->assertInstanceOf(ApiException::class, new RateLimitException());
    }

    public function testCustomMessage(): void
    {
        $e = new RateLimitException('Too fast', 60);

        $this->assertSame('Too fast', $e->getMessage());
        $this->assertSame(60, $e->retryAfter);
    }
}
