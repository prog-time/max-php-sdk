<?php

/**
 * Example: send a message with a video attachment.
 *
 * Flow for video / audio:
 *   1. POST /uploads?type=video — the API returns {url, token}.
 *      Save the token: it is the attachment token for the message.
 *   2. Upload the video file to the returned URL via multipart/form-data.
 *      The CDN returns {retval: ...} — this is not the attachment token.
 *   3. Send a message using the token from step 1.
 *
 * uploadFile() handles all three steps automatically.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MaxBotApi\Config;
use MaxBotApi\MaxClient;
use MaxBotApi\Resources\Uploads;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\RateLimitException;

$client = new MaxClient(new Config('your-bot-token'));

$chatId    = 123456789;
$videoPath = __DIR__ . '/files/movie.mp4';

// Step 1 + 2: upload and get the token.
$token = $client->uploads->uploadFile($videoPath, Uploads::TYPE_VIDEO);

// The video may still be processing on the server side.
// Retry sending with exponential back-off if attachment.not.ready is returned.
$delay    = 2;
$maxTries = 5;

for ($attempt = 1; $attempt <= $maxTries; $attempt++) {
    try {
        $message = $client->messages->send(
            text:        'Check out this video!',
            chatId:      $chatId,
            attachments: [
                [
                    'type'    => 'video',
                    'payload' => ['token' => $token],
                ],
            ],
        );

        echo 'Message sent: ' . $message->messageId . PHP_EOL;
        break;

    } catch (ApiException $e) {
        if ($e->getMessage() === 'attachment.not.ready' && $attempt < $maxTries) {
            echo "Attachment not ready, retrying in {$delay}s... (attempt {$attempt}/{$maxTries})" . PHP_EOL;
            sleep($delay);
            $delay *= 2; // exponential back-off
        } else {
            throw $e;
        }
    }
}
