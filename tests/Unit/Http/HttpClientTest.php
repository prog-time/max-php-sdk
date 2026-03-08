<?php

declare(strict_types=1);

namespace MaxBotApi\Tests\Unit\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MaxBotApi\Config;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class HttpClientTest extends TestCase
{
    /**
     * @param list<Response|ConnectException> $responses
     */
    private function makeClient(array $responses): HttpClient
    {
        $config = new Config('test-token');
        $http   = new HttpClient($config);

        $mock    = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $guzzle  = new GuzzleClient(['handler' => $handler, 'http_errors' => false]);

        $ref = new ReflectionProperty(HttpClient::class, 'client');
        $ref->setAccessible(true);
        $ref->setValue($http, $guzzle);

        return $http;
    }

    private function makeTempFile(string $content = 'fake file content'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'max_sdk_test_');

        if ($path === false) {
            $this->fail('Could not create temporary file');
        }

        file_put_contents($path, $content);

        return $path;
    }

    // -------------------------------------------------------------------------
    // request()
    // -------------------------------------------------------------------------

    public function testSuccessfulGetRequestReturnsDecodedJson(): void
    {
        $http = $this->makeClient([
            new Response(200, [], (string) json_encode(['user_id' => 1, 'name' => 'Bot'])),
        ]);

        $result = $http->request('GET', '/me');

        $this->assertSame(1, $result['user_id']);
        $this->assertSame('Bot', $result['name']);
    }

    public function testSuccessfulPostRequestReturnsDecodedJson(): void
    {
        $body = ['message' => ['body' => ['mid' => 'x'], 'timestamp' => 0]];
        $http = $this->makeClient([new Response(200, [], (string) json_encode($body))]);

        $result = $http->request('POST', '/messages', ['text' => 'Hello'], ['chat_id' => 5]);

        $this->assertArrayHasKey('message', $result);
    }

    public function testEmptyResponseBodyReturnsEmptyArray(): void
    {
        $http = $this->makeClient([new Response(200, [], '')]);

        $result = $http->request('GET', '/whatever');

        $this->assertSame([], $result);
    }

    public function test429WithoutRetryAfterThrowsRateLimitException(): void
    {
        $http = $this->makeClient([
            new Response(429, [], (string) json_encode(['error' => 'rate_limit'])),
        ]);

        $this->expectException(RateLimitException::class);
        $http->request('GET', '/me');
    }

    public function test429WithRetryAfterHeaderSetsRetryAfterValue(): void
    {
        $http = $this->makeClient([
            new Response(429, ['Retry-After' => '60'], (string) json_encode(['error' => 'rate_limit'])),
        ]);

        try {
            $http->request('GET', '/me');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame(60, $e->retryAfter);
        }
    }

    public function test429WithoutRetryAfterHeaderHasNullRetryAfter(): void
    {
        $http = $this->makeClient([
            new Response(429, [], (string) json_encode(['error' => 'rate_limit'])),
        ]);

        try {
            $http->request('GET', '/me');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertNull($e->retryAfter);
        }
    }

    public function test401ThrowsApiExceptionWithMessageFromResponse(): void
    {
        $http = $this->makeClient([
            new Response(401, [], (string) json_encode(['message' => 'Unauthorized'])),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unauthorized');
        $this->expectExceptionCode(401);
        $http->request('GET', '/me');
    }

    public function test404ThrowsApiExceptionWithErrorField(): void
    {
        $http = $this->makeClient([
            new Response(404, [], (string) json_encode(['error' => 'not_found'])),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);
        $http->request('GET', '/chats/999');
    }

    public function test500ThrowsApiException(): void
    {
        $http = $this->makeClient([
            new Response(500, [], (string) json_encode(['error' => 'Internal error'])),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);
        $http->request('GET', '/me');
    }

    public function testNetworkExceptionOnConnectionFailure(): void
    {
        $http = $this->makeClient([
            new ConnectException('Connection refused', new Request('GET', '/me')),
        ]);

        $this->expectException(NetworkException::class);
        $http->request('GET', '/me');
    }

    public function testApiExceptionFallsBackToGenericMessageWhenBothFieldsMissing(): void
    {
        $http = $this->makeClient([
            new Response(400, [], (string) json_encode([])),
        ]);

        try {
            $http->request('GET', '/me');
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame('API error', $e->getMessage());
            $this->assertSame(400, $e->getCode());
        }
    }

    public function testAuthorizationHeaderIsSent(): void
    {
        $capturedRequest = null;

        $mock    = new MockHandler([new Response(200, [], '{}')]);
        $handler = HandlerStack::create($mock);
        $handler->push(function (callable $next) use (&$capturedRequest) {
            return function ($request, $options) use ($next, &$capturedRequest) {
                $capturedRequest = $request;
                return $next($request, $options);
            };
        });

        $config = new Config('Bearer my-test-token');
        $http   = new HttpClient($config);

        $ref = new ReflectionProperty(HttpClient::class, 'client');
        $ref->setAccessible(true);
        $ref->setValue($http, new GuzzleClient(['handler' => $handler, 'http_errors' => false]));

        $http->request('GET', '/me');

        $this->assertNotNull($capturedRequest);
        $this->assertSame('Bearer my-test-token', $capturedRequest->getHeaderLine('Authorization'));
    }

    // -------------------------------------------------------------------------
    // uploadMultipart()
    // -------------------------------------------------------------------------

    public function testUploadMultipartSuccessReturnsDecodedJson(): void
    {
        $tmpFile = $this->makeTempFile('fake image content');

        $http = $this->makeClient([
            new Response(200, [], (string) json_encode(['token' => 'cdn-token-abc'])),
        ]);

        $result = $http->uploadMultipart('https://cdn.example.com/upload', $tmpFile);

        unlink($tmpFile);

        $this->assertSame('cdn-token-abc', $result['token']);
    }

    public function testUploadMultipartEmptyResponseBodyReturnsEmptyArray(): void
    {
        $tmpFile = $this->makeTempFile();

        $http = $this->makeClient([new Response(200, [], '')]);

        $result = $http->uploadMultipart('https://cdn.example.com/upload', $tmpFile);

        unlink($tmpFile);

        $this->assertSame([], $result);
    }

    public function testUploadMultipartReturnsFullCdnResponse(): void
    {
        $tmpFile = $this->makeTempFile('video content');

        $cdnPayload = ['token' => 'vid-tok', 'duration' => 120, 'size' => 2048];
        $http = $this->makeClient([
            new Response(200, [], (string) json_encode($cdnPayload)),
        ]);

        $result = $http->uploadMultipart('https://vu.mycdn.me/upload.do', $tmpFile);

        unlink($tmpFile);

        $this->assertSame('vid-tok', $result['token']);
        $this->assertSame(120, $result['duration']);
    }

    public function testUploadMultipartNetworkErrorThrowsNetworkException(): void
    {
        $tmpFile = $this->makeTempFile();

        $http = $this->makeClient([
            new ConnectException('Connection failed', new Request('POST', 'https://cdn.example.com/upload')),
        ]);

        try {
            $this->expectException(NetworkException::class);
            $http->uploadMultipart('https://cdn.example.com/upload', $tmpFile);
        } finally {
            unlink($tmpFile);
        }
    }
}
