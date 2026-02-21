<?php

declare(strict_types=1);

namespace MaxBotApi\DTO;

/**
 * Represents an incoming update event from the Max API.
 *
 * Payload fields depend on update_type and are merged into the root of the API response object.
 * Access them via $update->payload['key'].
 *
 * Supported update types and their payload keys:
 *
 *   message_created          — message, user_locale
 *   message_callback         — callback (callback_id, payload, user), message, user_locale
 *   message_edited           — message
 *   message_removed          — message_id, chat_id, user_id
 *   bot_started              — chat_id, user, payload, user_locale
 *   bot_stopped              — chat_id, user
 *   bot_added                — chat_id, user, is_channel
 *   bot_removed              — chat_id, user, is_channel
 *   user_added               — chat_id, user, inviter_id, is_channel
 *   user_removed             — chat_id, user, admin_id, is_channel
 *   chat_title_changed       — chat_id, user, title
 *   message_chat_created     — chat, message_id, start_payload
 *   message_construction_request — user, user_locale, session_id, data, input
 *   message_constructed      — user, session_id, message
 */
final class Update
{
    /**
     * @param string               $updateType Event type identifier.
     * @param int                  $timestamp  Unix timestamp in milliseconds.
     * @param array<string, mixed> $payload    All event-specific fields from the API response.
     */
    public function __construct(
        public readonly string $updateType,
        public readonly int    $timestamp,
        public readonly array  $payload,
    ) {}

    /**
     * @param array<string, mixed> $d Raw API response data.
     */
    public static function fromArray(array $d): self
    {
        return new self(
            updateType: $d['update_type'],
            timestamp:  $d['timestamp'],
            payload:    array_diff_key($d, array_flip(['update_type', 'timestamp'])),
        );
    }
}
