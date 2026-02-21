<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\DTO;

use MaxBotApi\DTO\UploadResult;
use PHPUnit\Framework\TestCase;

final class UploadResultTest extends TestCase
{
    public function testFromArrayWithToken(): void
    {
        $result = UploadResult::fromArray([
            'url'   => 'https://upload.example.com/file',
            'token' => 'my-token-abc',
        ]);

        $this->assertSame('https://upload.example.com/file', $result->url);
        $this->assertSame('my-token-abc', $result->token);
    }

    public function testFromArrayWithoutTokenIsNull(): void
    {
        $result = UploadResult::fromArray(['url' => 'https://upload.example.com/img']);

        $this->assertSame('https://upload.example.com/img', $result->url);
        $this->assertNull($result->token);
    }

    public function testFromArrayPreservesUrl(): void
    {
        $url = 'https://upload.example.com/video?sig=abc123&expires=9999';
        $result = UploadResult::fromArray(['url' => $url, 'token' => 'tok']);

        $this->assertSame($url, $result->url);
    }
}
