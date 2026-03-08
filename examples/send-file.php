<?php

/**
 * Example: send a message with a generic file attachment (PDF, ZIP, DOCX, etc.).
 *
 * Flow for image / file:
 *   1. POST /uploads?type=file — the API returns {url} (no token yet).
 *   2. Upload the file to the returned URL via multipart/form-data.
 *      The CDN returns {token: "..."}.
 *   3. Send a message using that token.
 *
 * uploadFile() handles all three steps automatically.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MaxBotApi\Config;
use MaxBotApi\MaxClient;
use MaxBotApi\Resources\Uploads;

$client = new MaxClient(new Config('your-bot-token'));

$chatId   = 123456789;
$filePath = __DIR__ . '/files/document.pdf';

$token = $client->uploads->uploadFile($filePath, Uploads::TYPE_FILE);

$message = $client->messages->send(
    text:        'Here is the document.',
    chatId:      $chatId,
    attachments: [
        [
            'type'    => 'file',
            'payload' => ['token' => $token],
        ],
    ],
);

echo 'Message sent: ' . $message->messageId . PHP_EOL;
