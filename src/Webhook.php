<?php

declare(strict_types=1);

namespace MaxBotApi;

/**
 * Parses incoming webhook payloads sent by the Max API.
 */
final class Webhook
{
    /**
     * Read and decode the JSON payload from the current HTTP request body.
     *
     * @return array<string, mixed>
     */
    public static function parse(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
