<?php

/**
 * Example: send a message with multiple attachments (e.g. several images).
 *
 * Upload each file separately, collect tokens, then send one message.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MaxBotApi\Config;
use MaxBotApi\MaxClient;
use MaxBotApi\Resources\Uploads;

$client = new MaxClient(new Config('your-bot-token'));

$chatId = 123456789;
$images = [
    __DIR__ . '/files/photo1.jpg',
    __DIR__ . '/files/photo2.jpg',
    __DIR__ . '/files/photo3.jpg',
];

// Upload all images and build the attachments array.
$attachments = [];

foreach ($images as $path) {
    $token         = $client->uploads->uploadFile($path, Uploads::TYPE_IMAGE);
    $attachments[] = [
        'type'    => 'image',
        'payload' => ['token' => $token],
    ];
}

$message = $client->messages->send(
    text:        'Here are the photos!',
    chatId:      $chatId,
    attachments: $attachments,
);

echo 'Message sent: ' . $message->messageId . PHP_EOL;
