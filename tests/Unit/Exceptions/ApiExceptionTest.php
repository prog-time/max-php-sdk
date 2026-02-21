<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Exceptions;

use MaxBotApi\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class ApiExceptionTest extends TestCase
{
    public function testExceptionHasCorrectMessageAndCode(): void
    {
        $e = new ApiException('Not Found', 404);

        $this->assertSame('Not Found', $e->getMessage());
        $this->assertSame(404, $e->getCode());
    }

    public function testExceptionIsRuntimeException(): void
    {
        $this->assertInstanceOf(\RuntimeException::class, new ApiException('error', 500));
    }

    public function testExceptionCanChainPrevious(): void
    {
        $previous = new \RuntimeException('original');
        $e = new ApiException('wrapped', 500, $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
