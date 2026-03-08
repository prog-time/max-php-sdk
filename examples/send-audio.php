<?php

/**
 * Example: send a message with an audio attachment.
 *
 * Audio follows the same token flow as video:
 *   token comes from POST /uploads, not from the CDN upload response.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MaxBotApi\Config;
use MaxBotApi\MaxClient;
use MaxBotApi\Resources\Uploads;
use MaxBotApi\Exceptions\ApiException;

$client = new MaxClient(new Config('your-bot-token'));

$chatId    = 123456789;
$audioPath = __DIR__ . '/files/track.mp3';

$token = $client->uploads->uploadFile($audioPath, Uploads::TYPE_AUDIO);

$delay    = 2;
$maxTries = 5;

for ($attempt = 1; $attempt <= $maxTries; $attempt++) {
    try {
        $message = $client->messages->send(
            text:        'Listen to this!',
            chatId:      $chatId,
            attachments: [
                [
                    'type'    => 'audio',
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
            $delay *= 2;
        } else {
            throw $e;
        }
    }
}
