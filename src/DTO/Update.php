<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Payload fields depend on update_type and are merged into the root of the update object.
 *
 * message_created:          payload['message'], payload['user_locale']
 * message_callback:         payload['callback']['callback_id|payload|user'], payload['message']
 * message_edited:           payload['message']
 * message_removed:          payload['message_id'], payload['chat_id'], payload['user_id']
 * bot_started:              payload['chat_id'], payload['user'], payload['payload']
 * bot_added / bot_removed:  payload['chat_id'], payload['user'], payload['is_channel']
 * user_added / user_removed: payload['chat_id'], payload['user'], payload['is_channel']
 * chat_title_changed:       payload['chat_id'], payload['user'], payload['title']
 * message_chat_created:     payload['chat'], payload['message_id'], payload['start_payload']
 * message_construction_request / message_constructed: payload['user'], payload['session_id'], ...
 */
final class Update
{
    public function __construct(
        public string $updateType,
        public int    $timestamp,
        /** @var array<string, mixed> */
        public array  $payload,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            updateType: $d['update_type'],
            timestamp:  $d['timestamp'],
            payload:    array_diff_key($d, array_flip(['update_type', 'timestamp'])),
        );
    }
}
