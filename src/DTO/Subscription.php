<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

final class Subscription
{
    public function __construct(
        public string $url,
        public int    $time,
        /** @var string[] */
        public array  $updateTypes,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            url:         $d['url'],
            time:        $d['time'],
            updateTypes: $d['update_types'] ?? [],
        );
    }
}
