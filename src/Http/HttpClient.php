<?php

declare(strict_types=1);

namespace MaxBotApi\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MaxBotApi\Config;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;

final class HttpClient
{
    private Client $client;

    public function __construct(private Config $config)
    {
        $this->client = new Client([
            'base_uri'    => $config->baseUrl,
            'timeout'     => $config->timeout,
            'http_errors' => false,
        ]);
    }

    /**
     * @param array<string, mixed> $body  JSON body (POST/PUT/PATCH)
     * @param array<string, mixed> $query URL query parameters
     * @return array<string, mixed>
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
                retryAfter: $retryAfter !== '' ? (int) $retryAfter : null
            );
        }

        if ($status >= 400) {
            throw new ApiException($data['message'] ?? $data['error'] ?? 'API error', $status);
        }

        return $data;
    }
}
