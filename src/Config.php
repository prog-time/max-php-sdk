<?php

namespace MaxBotApi;

final class Config
{
    public function __construct(
        public string $token,
        public string $baseUrl = 'https://platform-api.max.ru',
        public int $timeout = 10
    ) {}
}
