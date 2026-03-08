<?php

declare(strict_types=1);

namespace MaxBotApi\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MaxBotApi\Config;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;

/**
 * HTTP client wrapping Guzzle for communication with the Max Bot API.
 */
final class HttpClient
{
    private Client $client;

    /**
     * @param Config $config SDK configuration used to initialise the Guzzle client.
     */
    public function __construct(private readonly Config $config)
    {
        $this->client = new Client([
            'base_uri'    => $config->baseUrl,
            'timeout'     => $config->timeout,
            'http_errors' => false,
        ]);
    }

    /**
     * Send an HTTP request to the Max API and return the decoded JSON response.
     *
     * @param string               $method HTTP method: GET, POST, PUT, PATCH, DELETE.
     * @param string               $uri    API endpoint path, e.g. '/messages'.
     * @param array<string, mixed> $body   JSON request body, used for POST / PUT / PATCH.
     * @param array<string, mixed> $query  URL query string parameters.
     *
     * @return array<string, mixed>
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure, timeout, or DNS error.
     */
    public function request(string $method, string $uri, array $body = [], array $query = []): array
    {
        $options = [
            'headers' => [
                'Authorization' => $this->config->token,
                'Content-Type'  => 'application/json',
            ],
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['json'] = $body;
        }

        try {
            $res = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new NetworkException($e->getMessage(), 0, $e);
        }

        $status = $res->getStatusCode();
        $data   = json_decode($res->getBody()->getContents(), true) ?? [];

        if ($status === 429) {
            $retryAfter = $res->getHeaderLine('Retry-After');
            throw new RateLimitException(
                retryAfter: $retryAfter !== '' ? (int) $retryAfter : null,
            );
        }

        if ($status >= 400) {
            throw new ApiException($data['message'] ?? $data['error'] ?? 'API error', $status);
        }

        return $data;
    }

    /**
     * Upload a local file to an external pre-signed URL using multipart/form-data.
     *
     * Used internally by Uploads::uploadFile() to send the file to the CDN.
     *
     * @param string $url      Pre-signed upload URL (e.g. from Max media CDN).
     * @param string $filePath Absolute or relative path to the file on disk.
     *
     * @return array<string, mixed> Decoded JSON response from the CDN.
     *
     * @throws NetworkException On connection failure or timeout.
     */
    public function uploadMultipart(string $url, string $filePath): array
    {
        $options = [
            'multipart' => [
                [
                    'name'     => 'data',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
            ],
        ];

        try {
            $res = $this->client->post($url, $options);
        } catch (GuzzleException $e) {
            throw new NetworkException($e->getMessage(), 0, $e);
        }

        return json_decode($res->getBody()->getContents(), true) ?? [];
    }
}
