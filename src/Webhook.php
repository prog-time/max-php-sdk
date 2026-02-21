<?php

namespace MaxBotApi;

final class Webhook
{
    public static function parse(): array
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}
