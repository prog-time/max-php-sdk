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

    /**
     * Upload a local file and return the attachment token for use in a message.
     *
     * This method handles the full two-step process:
     *   1. Request a pre-signed upload URL from the Max API.
     *   2. Upload the file to that URL via multipart/form-data.
     *
     * Token sources differ by type:
     *   - image / file : token is returned in the CDN upload response.
     *   - video / audio: token is returned by the initial /uploads request
     *                    and becomes usable after the upload completes.
     *
     * @param string $filePath Absolute or relative path to the file on disk.
     * @param string $type     File type. Use one of the TYPE_* constants.
     *
     * @throws \RuntimeException  If no token is found in the API responses.
     * @throws RateLimitException On HTTP 429 Too Many Requests.
     * @throws ApiException       On HTTP 4xx or 5xx error response.
     * @throws NetworkException   On connection failure or timeout.
     */
    public function uploadFile(string $filePath, string $type = self::TYPE_FILE): string
    {
        $uploadResult = $this->getUploadUrl($type);

        // Upload the file to the pre-signed CDN URL.
        $cdnResponse = $this->http->uploadMultipart($uploadResult->url, $filePath);

        // video / audio: token comes from the /uploads response.
        // image:         CDN returns {"photos":{"<key>":{"token":"..."}}}
        // file:          CDN returns {"token":"..."} directly.
        $cdnToken = $cdnResponse['token']
            ?? (isset($cdnResponse['photos']) ? reset($cdnResponse['photos'])['token'] ?? null : null)
            ?? null;

        $token = $uploadResult->token ?? $cdnToken ?? null;

        if ($token === null) {
            throw new \RuntimeException(
                'No attachment token received. CDN response: ' . json_encode($cdnResponse)
            );
        }

        return $token;
    }
}
