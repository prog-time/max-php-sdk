<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\UploadResult;
use MaxBotApi\Http\HttpClient;

final class Uploads
{
    // Supported file types
    public const TYPE_IMAGE = 'image'; // JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC
    public const TYPE_VIDEO = 'video'; // MP4, MOV, MKV, WEBM
    public const TYPE_AUDIO = 'audio'; // MP3, WAV, M4A
    public const TYPE_FILE  = 'file';  // Any file type

    public function __construct(private HttpClient $http) {}

    /**
     * Get an upload URL for a file.
     * After receiving the URL, upload the file with a separate request.
     * For video/audio the token from the response must be used when sending a message.
     */
    public function getUploadUrl(string $type = self::TYPE_FILE): UploadResult
    {
        $data = $this->http->request('POST', '/uploads', [], ['type' => $type]);

        return UploadResult::fromArray($data);
    }
}
