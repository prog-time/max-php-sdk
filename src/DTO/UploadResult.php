<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

final class UploadResult
{
    public function __construct(
        public string  $url,
        public ?string $token,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            url:   $d['url'],
            token: $d['token'] ?? null,
        );
    }
}
