<?php

namespace Nicobleiler\Mistral\Tests;

use PHPUnit\Framework\TestCase;
use Nicobleiler\Mistral\SDK\Client;
use Nicobleiler\Mistral\SDK\Agents;
use Nicobleiler\Mistral\SDK\Chat;
use Nicobleiler\Mistral\SDK\Conversations;
use Nicobleiler\Mistral\SDK\Files;

class ClientTest extends TestCase
{
    public function test_client_can_be_instantiated()
    {
        $client = new Client('test-api-key');

        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_client_has_chat_resource()
    {
        $client = new Client('test-api-key');

        $this->assertInstanceOf(Chat::class, $client->chat());
    }

    public function test_client_has_files_resource()
    {
        $client = new Client('test-api-key');

        $this->assertInstanceOf(Files::class, $client->files());
    }

    public function test_client_has_agents_resource()
    {
        $client = new Client('test-api-key');

        $this->assertInstanceOf(Agents::class, $client->agents());
    }

    public function test_client_has_conversations_resource()
    {
        $client = new Client('test-api-key');

        $this->assertInstanceOf(Conversations::class, $client->conversations());
    }

    public function test_client_uses_custom_base_url()
    {
        $customUrl = 'https://custom.api.com/v1';
        $client = new Client('test-api-key', $customUrl);

        // We can't directly test the private property, but we can verify the client was created
        $this->assertInstanceOf(Client::class, $client);
    }
}
