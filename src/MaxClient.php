<?php

declare(strict_types=1);

namespace MaxBotApi;

use MaxBotApi\Http\HttpClient;
use MaxBotApi\Resources\Bot;
use MaxBotApi\Resources\ChatMembers;
use MaxBotApi\Resources\Chats;
use MaxBotApi\Resources\Messages;
use MaxBotApi\Resources\Subscriptions;
use MaxBotApi\Resources\Uploads;

final class MaxClient
{
    public Bot           $bot;
    public Messages      $messages;
    public Chats         $chats;
    public ChatMembers   $chatMembers;
    public Subscriptions $subscriptions;
    public Uploads       $uploads;

    public function __construct(Config $config)
    {
        $http = new HttpClient($config);

        $this->bot           = new Bot($http);
        $this->messages      = new Messages($http);
        $this->chats         = new Chats($http);
        $this->chatMembers   = new ChatMembers($http);
        $this->subscriptions = new Subscriptions($http);
        $this->uploads       = new Uploads($http);
    }
}
