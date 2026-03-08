<?php

/**
 * Example: send a message with an image attachment.
 *
 * Flow:
 *   1. Upload the image file — the CDN returns a token.
 *   2. Send a message with that token as an attachment.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MaxBotApi\Config;
use MaxBotApi\MaxClient;
use MaxBotApi\Resources\Uploads;

$client = new MaxClient(new Config('your-bot-token'));

$chatId    = 123456789;
$imagePath = __DIR__ . '/files/photo.jpg'; // local path to the image

// Step 1: upload the image and get the token.
$token = $client->uploads->uploadFile($imagePath, Uploads::TYPE_IMAGE);

// Step 2: send the message with the image attachment.
$message = $client->messages->send(
    text:        'Here is your image!',
    chatId:      $chatId,
    attachments: [
        [
            'type'    => 'image',
            'payload' => ['token' => $token],
        ],
    ],
);

echo 'Message sent: ' . $message->messageId . PHP_EOL;
