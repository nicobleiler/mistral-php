<?php

namespace Mistral\Tests\Mcp;

use PHPUnit\Framework\TestCase;
use Mistral\Client;
use Mistral\Mcp\MistralMcpElements;
use Mistral\Mcp\MistralMcpServer;
use Psr\Log\NullLogger;
use Mockery;

class MistralMcpTest extends TestCase
{
    private Client $mockClient;
    private MistralMcpElements $elements;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockClient = Mockery::mock(Client::class);
        $this->elements = new MistralMcpElements($this->mockClient, new NullLogger());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_mcp_elements()
    {
        $this->assertInstanceOf(MistralMcpElements::class, $this->elements);
    }

    public function test_can_create_mcp_server()
    {
        $server = new MistralMcpServer($this->mockClient, new NullLogger());
        
        $this->assertInstanceOf(MistralMcpServer::class, $server);
        $this->assertInstanceOf(\Mcp\Server::class, $server->getServer());
        $this->assertInstanceOf(MistralMcpElements::class, $server->getElements());
    }

    public function test_can_create_mcp_server_from_static_method()
    {
        $server = MistralMcpServer::create('test-api-key');
        
        $this->assertInstanceOf(MistralMcpServer::class, $server);
    }

    public function test_client_can_create_mcp_server()
    {
        $client = new Client('test-api-key');
        $server = $client->createMcpServer();
        
        $this->assertInstanceOf(MistralMcpServer::class, $server);
    }

    public function test_chat_tool_returns_error_on_exception()
    {
        $chatResource = Mockery::mock(\Mistral\Resources\Chat::class);
        $chatResource->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $this->mockClient->shouldReceive('chat')
            ->once()
            ->andReturn($chatResource);

        $result = $this->elements->chat('mistral-tiny', 'Hello');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Failed to generate chat completion', $result['error']);
        $this->assertEquals('mistral-tiny', $result['model']);
        $this->assertEquals('', $result['content']);
    }

    public function test_embed_tool_returns_error_on_exception()
    {
        $embeddingsResource = Mockery::mock(\Mistral\Resources\Embeddings::class);
        $embeddingsResource->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $this->mockClient->shouldReceive('embeddings')
            ->once()
            ->andReturn($embeddingsResource);

        $result = $this->elements->embed('Hello world');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Failed to generate embeddings', $result['error']);
        $this->assertEquals('mistral-embed', $result['model']);
        $this->assertEquals([], $result['embeddings']);
    }

    public function test_list_models_tool_returns_error_on_exception()
    {
        $modelsResource = Mockery::mock(\Mistral\Resources\Models::class);
        $modelsResource->shouldReceive('list')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $this->mockClient->shouldReceive('models')
            ->once()
            ->andReturn($modelsResource);

        $result = $this->elements->listModels();

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Failed to list models', $result['error']);
        $this->assertEquals([], $result['models']);
    }

    public function test_get_model_tool_returns_error_on_exception()
    {
        $modelsResource = Mockery::mock(\Mistral\Resources\Models::class);
        $modelsResource->shouldReceive('get')
            ->once()
            ->with('mistral-tiny')
            ->andThrow(new \Exception('API Error'));

        $this->mockClient->shouldReceive('models')
            ->once()
            ->andReturn($modelsResource);

        $result = $this->elements->getModel('mistral-tiny');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Failed to get model details', $result['error']);
        $this->assertNull($result['model']);
    }

    public function test_models_info_resource_returns_expected_structure()
    {
        $result = $this->elements->getModelsInfo();

        $this->assertArrayHasKey('models', $result);
        $this->assertIsArray($result['models']);
        $this->assertGreaterThan(0, count($result['models']));

        $firstModel = $result['models'][0];
        $this->assertArrayHasKey('id', $firstModel);
        $this->assertArrayHasKey('description', $firstModel);
        $this->assertArrayHasKey('capabilities', $firstModel);
        $this->assertIsArray($firstModel['capabilities']);
    }

    public function test_client_config_resource_returns_expected_structure()
    {
        $result = $this->elements->getClientConfig();

        $this->assertArrayHasKey('base_url', $result);
        $this->assertArrayHasKey('timeout', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertEquals('https://api.mistral.ai/v1', $result['base_url']);
        $this->assertEquals(30, $result['timeout']);
        $this->assertEquals('1.0.0', $result['version']);
    }
}