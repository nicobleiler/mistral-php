<?php

namespace Nicobleiler\Mistral\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Nicobleiler\Mistral\Resources\Agents;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class AgentsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_agents_can_be_instantiated()
    {
        $client = Mockery::mock(Client::class);
        $agents = new Agents($client);

        $this->assertInstanceOf(Agents::class, $agents);
    }

    public function test_agents_create_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'asst_abc123',
            'object' => 'agent',
            'created_at' => 1234567890,
            'name' => 'Math Tutor',
            'description' => 'Math Tutor Agent',
            'model' => 'mistral-large',
            'instructions' => 'You are a personal math tutor.',
            'tools' => [],
            'file_ids' => [],
            'metadata' => []
        ]);

        $params = [
            'model' => 'mistral-large',
            'name' => 'Math Tutor',
            'description' => 'Math Tutor Agent',
            'instructions' => 'You are a personal math tutor.'
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/agents', ['json' => $params])
            ->andReturn(new Response(200, [], $expectedResponse));

        $agents = new Agents($client);
        $result = $agents->create($params);

        $this->assertEquals('asst_abc123', $result['id']);
        $this->assertEquals('Math Tutor', $result['name']);
    }

    public function test_agents_list_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'asst_abc123',
                    'object' => 'agent',
                    'created_at' => 1234567890,
                    'name' => 'Math Tutor',
                    'model' => 'mistral-large'
                ]
            ],
            'first_id' => 'asst_abc123',
            'last_id' => 'asst_abc123',
            'has_more' => false
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/agents', ['query' => []])
            ->andReturn(new Response(200, [], $expectedResponse));

        $agents = new Agents($client);
        $result = $agents->list();

        $this->assertEquals('list', $result['object']);
        $this->assertFalse($result['has_more']);
    }

    public function test_agents_retrieve_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'asst_abc123',
            'object' => 'agent',
            'created_at' => 1234567890,
            'name' => 'Math Tutor',
            'model' => 'mistral-large',
            'instructions' => 'You are a personal math tutor.',
            'tools' => [],
            'file_ids' => [],
            'metadata' => []
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/agents/asst_abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $agents = new Agents($client);
        $result = $agents->retrieve('asst_abc123');

        $this->assertEquals('asst_abc123', $result['id']);
        $this->assertEquals('Math Tutor', $result['name']);
    }

    public function test_agents_update_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'asst_abc123',
            'object' => 'agent',
            'created_at' => 1234567890,
            'name' => 'Advanced Math Tutor',
            'model' => 'mistral-large',
            'instructions' => 'You are an advanced math tutor.',
            'tools' => [],
            'file_ids' => [],
            'metadata' => []
        ]);

        $params = [
            'name' => 'Advanced Math Tutor',
            'instructions' => 'You are an advanced math tutor.'
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/agents/asst_abc123', ['json' => $params])
            ->andReturn(new Response(200, [], $expectedResponse));

        $agents = new Agents($client);
        $result = $agents->update('asst_abc123', $params);

        $this->assertEquals('Advanced Math Tutor', $result['name']);
    }

    public function test_agents_delete_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'asst_abc123',
            'object' => 'agent.deleted',
            'deleted' => true
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('DELETE', '/v1/agents/asst_abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $agents = new Agents($client);
        $result = $agents->delete('asst_abc123');

        $this->assertTrue($result['deleted']);
    }
}
