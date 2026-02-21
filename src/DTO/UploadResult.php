<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents a pre-signed upload URL returned by the Max API.
 *
 * Upload the file to $url using a separate HTTP request.
 * For video and audio, include the $token when sending a message.
 */
final class UploadResult
{
    /**
     * @param string      $url   Pre-signed URL to upload the file to.
     * @param string|null $token Upload token to reference the file in a message attachment.
     */
    public function __construct(
        public readonly string  $url,
        public readonly ?string $token,
    ) {}

    /**
     * @param array<string, mixed> $d Raw API response data.
     */
    public static function fromArray(array $d): self
    {
        return new self(
            url:   $d['url'],
            token: $d['token'] ?? null,
        );
    }
}
