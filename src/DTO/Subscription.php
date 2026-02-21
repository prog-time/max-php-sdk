<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents an active webhook subscription for the bot.
 */
final class Subscription
{
    /**
     * @param string   $url         Webhook URL receiving update events.
     * @param int      $time        Unix timestamp in milliseconds when the subscription was created.
     * @param string[] $updateTypes List of subscribed event type names.
     */
    public function __construct(
        public readonly string $url,
        public readonly int    $time,
        public readonly array  $updateTypes,
    ) {}

    /**
     * @param array<string, mixed> $d Raw API response data.
     */
    public static function fromArray(array $d): self
    {
        return new self(
            url:         $d['url'],
            time:        $d['time'],
            updateTypes: $d['update_types'] ?? [],
        );
    }
}
