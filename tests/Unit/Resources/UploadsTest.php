<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Resources;

use MaxBotApi\DTO\UploadResult;
use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\Uploads;
use PHPUnit\Framework\TestCase;

final class UploadsTest extends TestCase
{
    private function mockHttp(): HttpClient
    {
        return $this->createMock(HttpClient::class);
    }

    public function testGetUploadUrlDefaultTypeIsFile(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', [], ['type' => 'file'])
            ->willReturn(['url' => 'https://upload.example.com/file', 'token' => 'tok123']);

        $result = (new Uploads($http))->getUploadUrl();

        $this->assertInstanceOf(UploadResult::class, $result);
        $this->assertSame('https://upload.example.com/file', $result->url);
        $this->assertSame('tok123', $result->token);
    }

    public function testGetUploadUrlWithImageType(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', [], ['type' => 'image'])
            ->willReturn(['url' => 'https://upload.example.com/img']);

        $result = (new Uploads($http))->getUploadUrl(Uploads::TYPE_IMAGE);

        $this->assertNull($result->token);
    }

    public function testGetUploadUrlWithVideoType(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', [], ['type' => 'video'])
            ->willReturn(['url' => 'https://upload.example.com/video', 'token' => 'vid-tok']);

        $result = (new Uploads($http))->getUploadUrl(Uploads::TYPE_VIDEO);

        $this->assertSame('vid-tok', $result->token);
    }

    public function testGetUploadUrlWithAudioType(): void
    {
        $http = $this->mockHttp();
        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', [], ['type' => 'audio'])
            ->willReturn(['url' => 'https://upload.example.com/audio', 'token' => 'aud-tok']);

        (new Uploads($http))->getUploadUrl(Uploads::TYPE_AUDIO);
    }

    public function testTypeConstantsHaveCorrectValues(): void
    {
        $this->assertSame('image', Uploads::TYPE_IMAGE);
        $this->assertSame('video', Uploads::TYPE_VIDEO);
        $this->assertSame('audio', Uploads::TYPE_AUDIO);
        $this->assertSame('file', Uploads::TYPE_FILE);
    }
}
