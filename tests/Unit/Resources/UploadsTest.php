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

    // -------------------------------------------------------------------------
    // getUploadUrl()
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // uploadFile() — image and file: token comes from CDN upload response
    // -------------------------------------------------------------------------

    public function testUploadFileForImageUsesTokenFromCdnResponse(): void
    {
        $http = $this->mockHttp();

        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', [], ['type' => 'image'])
            ->willReturn(['url' => 'https://cdn.example.com/upload']);

        $http->expects($this->once())
            ->method('uploadMultipart')
            ->with('https://cdn.example.com/upload', '/tmp/photo.jpg')
            ->willReturn(['token' => 'img-token-abc']);

        $token = (new Uploads($http))->uploadFile('/tmp/photo.jpg', Uploads::TYPE_IMAGE);

        $this->assertSame('img-token-abc', $token);
    }

    public function testUploadFileForFileTypeUsesTokenFromCdnResponse(): void
    {
        $http = $this->mockHttp();

        $http->method('request')->willReturn(['url' => 'https://cdn.example.com/upload']);
        $http->method('uploadMultipart')->willReturn(['token' => 'file-token-xyz']);

        $token = (new Uploads($http))->uploadFile('/tmp/doc.pdf', Uploads::TYPE_FILE);

        $this->assertSame('file-token-xyz', $token);
    }

    public function testUploadFileDefaultTypeIsFile(): void
    {
        $http = $this->mockHttp();

        $http->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', [], ['type' => 'file'])
            ->willReturn(['url' => 'https://cdn.example.com/upload']);

        $http->method('uploadMultipart')->willReturn(['token' => 'default-file-tok']);

        $token = (new Uploads($http))->uploadFile('/tmp/archive.zip');

        $this->assertSame('default-file-tok', $token);
    }

    // -------------------------------------------------------------------------
    // uploadFile() — video and audio: token comes from getUploadUrl response
    // -------------------------------------------------------------------------

    public function testUploadFileForVideoUsesTokenFromGetUploadUrlResponse(): void
    {
        $http = $this->mockHttp();

        $http->method('request')->willReturn([
            'url'   => 'https://vu.mycdn.me/upload.do',
            'token' => 'vid-token-from-api',
        ]);
        // CDN returns retval for video, not a token — it must be ignored
        $http->method('uploadMultipart')->willReturn(['retval' => 'ok']);

        $token = (new Uploads($http))->uploadFile('/tmp/movie.mp4', Uploads::TYPE_VIDEO);

        $this->assertSame('vid-token-from-api', $token);
    }

    public function testUploadFileForAudioUsesTokenFromGetUploadUrlResponse(): void
    {
        $http = $this->mockHttp();

        $http->method('request')->willReturn([
            'url'   => 'https://au.mycdn.me/upload.do',
            'token' => 'aud-token-from-api',
        ]);
        $http->method('uploadMultipart')->willReturn([]);

        $token = (new Uploads($http))->uploadFile('/tmp/track.mp3', Uploads::TYPE_AUDIO);

        $this->assertSame('aud-token-from-api', $token);
    }

    public function testUploadFilePassesFilePathToUploadMultipart(): void
    {
        $http = $this->mockHttp();

        $http->method('request')->willReturn(['url' => 'https://cdn.example.com/upload']);

        $http->expects($this->once())
            ->method('uploadMultipart')
            ->with('https://cdn.example.com/upload', '/custom/path/image.png')
            ->willReturn(['token' => 'tok']);

        (new Uploads($http))->uploadFile('/custom/path/image.png', Uploads::TYPE_IMAGE);
    }

    // -------------------------------------------------------------------------
    // uploadFile() — error: no token in either response
    // -------------------------------------------------------------------------

    public function testUploadFileThrowsRuntimeExceptionWhenNoTokenFound(): void
    {
        $http = $this->mockHttp();

        $http->method('request')->willReturn(['url' => 'https://cdn.example.com/upload']);
        $http->method('uploadMultipart')->willReturn([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/No attachment token received/');

        (new Uploads($http))->uploadFile('/tmp/photo.jpg', Uploads::TYPE_IMAGE);
    }

    public function testUploadFileExceptionMessageContainsCdnResponse(): void
    {
        $http = $this->mockHttp();

        $http->method('request')->willReturn(['url' => 'https://cdn.example.com/upload']);
        $http->method('uploadMultipart')->willReturn(['status' => 'error', 'code' => 500]);

        try {
            (new Uploads($http))->uploadFile('/tmp/photo.jpg', Uploads::TYPE_IMAGE);
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CDN response', $e->getMessage());
        }
    }
}
