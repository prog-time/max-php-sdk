# Max Bot PHP SDK

PHP SDK for the [Max](https://max.ru) messenger Bot API.

## Requirements

- PHP 8.1+
- Composer

## Installation

```bash
composer require prog-time/max-php-sdk
```

## Quick start

```php
use MaxBotApi\MaxClient;
use MaxBotApi\Config;

$client = new MaxClient(new Config('your-bot-token'));

$client->messages->send(text: 'Hello!', chatId: 123456789);
```

## Configuration

```php
$config = new Config(
    token:   'your-bot-token',  // required
    baseUrl: 'https://platform-api.max.ru', // optional, default value
    timeout: 10,                // optional, seconds, default 10
);
```

---

## Resources

### Bot

```php
$bot = $client->bot->me();

echo $bot->name;       // display name
echo $bot->userId;     // int
echo $bot->username;   // string|null
```

---

### Messages

**Send a message**

```php
// to a chat
$message = $client->messages->send(text: 'Hello!', chatId: 123456789);

// to a user (direct message)
$message = $client->messages->send(text: 'Hello!', userId: 987654321);

// with Markdown formatting
$message = $client->messages->send(
    text:   '**Bold** and _italic_',
    chatId: 123456789,
    format: 'markdown',
);
```

**Get messages**

```php
$messages = $client->messages->list(chatId: 123456789, count: 20);

$message = $client->messages->get('message-id');
```

**Edit and delete**

```php
$client->messages->edit(messageId: 'message-id', text: 'Updated text');

$client->messages->delete('message-id');
```

**Answer a callback (inline button)**

```php
$client->messages->answerCallback(
    callbackId:   $update->payload['callback']['callback_id'],
    notification: 'Button pressed!',
);
```

---

### Chats

```php
$chats = $client->chats->list(count: 50);

$chat  = $client->chats->get(chatId: 123456789);

$client->chats->update(chatId: 123456789, title: 'New title');

$client->chats->sendAction(chatId: 123456789, action: 'typing_on');

$client->chats->pinMessage(chatId: 123456789, messageId: 'message-id');
$client->chats->unpinMessage(chatId: 123456789);

$client->chats->delete(chatId: 123456789);
```

Available sender actions: `typing_on`, `sending_photo`, `sending_video`, `sending_audio`, `sending_file`, `mark_seen`.

---

### Chat members

```php
$members = $client->chatMembers->list(chatId: 123456789);
$admins  = $client->chatMembers->listAdmins(chatId: 123456789);

$client->chatMembers->add(chatId: 123456789, userIds: [111, 222]);
$client->chatMembers->remove(chatId: 123456789, userId: 111);
$client->chatMembers->remove(chatId: 123456789, userId: 111, block: true);

$client->chatMembers->addAdmin(chatId: 123456789, userId: 111);
$client->chatMembers->removeAdmin(chatId: 123456789, userId: 111);

$me = $client->chatMembers->getMe(chatId: 123456789);
$client->chatMembers->leaveChat(chatId: 123456789);
```

---

### Subscriptions (webhooks)

```php
// Subscribe
$client->subscriptions->subscribe(
    url:         'https://example.com/webhook',
    updateTypes: ['message_created', 'bot_started'],
    secret:      'my-secret',
);

// List active subscriptions
$subscriptions = $client->subscriptions->list();

// Unsubscribe
$client->subscriptions->unsubscribe();
```

**Long polling** (development only):

```php
$updates = $client->subscriptions->getUpdates(limit: 10, timeout: 30);

foreach ($updates as $update) {
    echo $update->updateType . PHP_EOL;
    // $update->payload contains all event-specific fields
}
```

---

### Uploads

```php
use MaxBotApi\Resources\Uploads;

$result = $client->uploads->getUploadUrl(Uploads::TYPE_IMAGE);

// $result->url   — upload the file here with a separate HTTP PUT/POST request
// $result->token — use this token when sending a message with an attachment
```

Available types: `Uploads::TYPE_IMAGE`, `TYPE_VIDEO`, `TYPE_AUDIO`, `TYPE_FILE`.

---

## Webhooks

```php
use MaxBotApi\Webhook;

$data   = Webhook::parse();
$update = \MaxBotApi\DTO\Update::fromArray($data);

switch ($update->updateType) {
    case 'message_created':
        $message = $update->payload['message'];
        break;

    case 'bot_started':
        $user = $update->payload['user'];
        break;

    case 'message_callback':
        $callback = $update->payload['callback'];
        break;
}
```

Supported update types: `message_created`, `message_callback`, `message_edited`, `message_removed`, `bot_started`, `bot_stopped`, `bot_added`, `bot_removed`, `user_added`, `user_removed`, `chat_title_changed`, `message_chat_created`, `message_construction_request`, `message_constructed`.

---

## Error handling

```php
use MaxBotApi\Exceptions\RateLimitException;
use MaxBotApi\Exceptions\ApiException;
use MaxBotApi\Exceptions\NetworkException;

try {
    $client->messages->send(text: 'Hello!', chatId: 123456789);

} catch (RateLimitException $e) {
    // HTTP 429 — retry after the specified delay
    sleep($e->retryAfter ?? 60);

} catch (ApiException $e) {
    // HTTP 4xx / 5xx — API returned an error
    echo $e->getMessage();
    echo $e->getCode(); // HTTP status code

} catch (NetworkException $e) {
    // Connection failure, timeout, DNS error
    echo $e->getMessage();
}
```

Exception hierarchy:

```
\RuntimeException
├── MaxBotApi\Exceptions\ApiException        — HTTP 4xx / 5xx
│   └── MaxBotApi\Exceptions\RateLimitException  — HTTP 429, has $retryAfter
└── MaxBotApi\Exceptions\NetworkException    — network-level errors
```

---

## License

MIT
