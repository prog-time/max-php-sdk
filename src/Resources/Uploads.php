<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\UploadResult;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Http\HttpClient;

/**
 * Resource for file upload endpoints.
 */
final class Uploads
{
    public const TYPE_IMAGE = 'image'; // Supported formats: JPG, JPEG, PNG, GIF, TIFF, BMP, HEIC
    public const TYPE_VIDEO = 'video'; // Supported formats: MP4, MOV, MKV, WEBM
    public const TYPE_AUDIO = 'audio'; // Supported formats: MP3, WAV, M4A
    public const TYPE_FILE  = 'file';  // Any file type

    public function __construct(private readonly HttpClient $http) {}

    /**
     * Request a pre-signed URL for uploading a file to the Max media server.
     *
     * After receiving the URL, upload the file directly with a separate HTTP request.
     * For video and audio uploads, pass the returned $token when sending a message attachment.
     *
     * @param string $type File type. Use one of the TYPE_* constants.
     *
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function getUploadUrl(string $type = self::TYPE_FILE): UploadResult
    {
        $data = $this->http->request('POST', '/uploads', [], ['type' => $type]);

        return UploadResult::fromArray($data);
    }
}
