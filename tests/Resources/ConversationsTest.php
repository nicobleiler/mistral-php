<?php

namespace Nicobleiler\Mistral\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Nicobleiler\Mistral\Resources\Conversations;
use Nicobleiler\Mistral\Types\Conversations\Conversation;
use Nicobleiler\Mistral\Types\Conversations\ConversationRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class ConversationsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_conversations_can_be_instantiated()
    {
        $client = Mockery::mock(Client::class);
        $conversations = new Conversations($client);

        $this->assertInstanceOf(Conversations::class, $conversations);
    }

    public function test_conversations_create_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'conv-abc123',
            'object' => 'conversation',
            'created_at' => 1234567890,
            'agent_id' => 'agent-xyz789',
            'metadata' => ['key' => 'value']
        ]);

        $params = [
            'agent_id' => 'agent-xyz789',
            'metadata' => ['key' => 'value']
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/conversations', ['json' => $params])
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->create($params);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertEquals('conv-abc123', $result->id);
        $this->assertEquals('agent-xyz789', $result->agent_id);
    }

    public function test_conversations_create_with_typed_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'conv-abc123',
            'object' => 'conversation',
            'created_at' => 1234567890,
            'agent_id' => 'agent-xyz789'
        ]);

        $request = new ConversationRequest('agent-xyz789');

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/conversations', ['json' => $request->toArray()])
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->create($request);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertEquals('conv-abc123', $result->id);
    }

    public function test_conversations_list_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'conv-abc123',
                    'object' => 'conversation',
                    'created_at' => 1234567890,
                    'agent_id' => 'agent-xyz789'
                ]
            ],
            'has_more' => false
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/conversations', ['query' => []])
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->list();

        $this->assertEquals('list', $result['object']);
        $this->assertIsArray($result['data']);
        $this->assertInstanceOf(Conversation::class, $result['data'][0]);
        $this->assertFalse($result['has_more']);
    }

    public function test_conversations_retrieve_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'conv-abc123',
            'object' => 'conversation',
            'created_at' => 1234567890,
            'agent_id' => 'agent-xyz789'
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/conversations/conv-abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->retrieve('conv-abc123');

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertEquals('conv-abc123', $result->id);
    }

    public function test_conversations_update_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'conv-abc123',
            'object' => 'conversation',
            'created_at' => 1234567890,
            'agent_id' => 'agent-xyz789',
            'metadata' => ['updated' => true]
        ]);

        $params = ['metadata' => ['updated' => true]];

        $client->shouldReceive('request')
            ->once()
            ->with('PATCH', '/v1/conversations/conv-abc123', ['json' => $params])
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->update('conv-abc123', $params);

        $this->assertInstanceOf(Conversation::class, $result);
        $this->assertEquals('conv-abc123', $result->id);
    }

    public function test_conversations_delete_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'conv-abc123',
            'object' => 'conversation.deleted',
            'deleted' => true
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('DELETE', '/v1/conversations/conv-abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->delete('conv-abc123');

        $this->assertEquals('conv-abc123', $result['id']);
        $this->assertTrue($result['deleted']);
    }

    public function test_conversations_list_handles_missing_keys_gracefully()
    {
        $client = Mockery::mock(Client::class);
        // Response missing 'object' and 'has_more' keys
        $expectedResponse = json_encode([
            'data' => []
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/conversations', ['query' => []])
            ->andReturn(new Response(200, [], $expectedResponse));

        $conversations = new Conversations($client);
        $result = $conversations->list();

        // Should use default values when keys are missing
        $this->assertEquals('list', $result['object']);
        $this->assertIsArray($result['data']);
        $this->assertEmpty($result['data']);
        $this->assertNull($result['first_id']);
        $this->assertNull($result['last_id']);
        $this->assertFalse($result['has_more']);
    }
}
