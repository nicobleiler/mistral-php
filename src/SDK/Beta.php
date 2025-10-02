<?php

namespace Nicobleiler\Mistral\SDK;

use GuzzleHttp\Client;
use Nicobleiler\Mistral\SDK\Beta\Agents;
use Nicobleiler\Mistral\SDK\Beta\Conversations;

class Beta
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function agents(): Agents
    {
        return new Agents($this->httpClient);
    }

    public function conversations(): Conversations
    {
        return new Conversations($this->httpClient);
    }
}
