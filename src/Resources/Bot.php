<?php

declare(strict_types=1);

namespace MaxBotApi\Resources;

use MaxBotApi\DTO\BotInfo;
use MaxBotApi\Http\HttpClient;

final class Bot
{
    public function __construct(private HttpClient $http) {}

    public function me(): BotInfo
    {
        $data = $this->http->request('GET', '/me');

        return BotInfo::fromArray($data);
    }
}
