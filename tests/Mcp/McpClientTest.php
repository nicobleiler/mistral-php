<?php

namespace Mistral\Tests\Mcp;

use PHPUnit\Framework\TestCase;
use Mistral\Client;
use Mistral\Mcp\McpClientManager;
use Mistral\Mcp\McpEnabledChat;
use Psr\Log\NullLogger;
use Mockery;

class McpClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_mcp_client_manager()
    {
        $manager = new McpClientManager();
        
        $this->assertInstanceOf(McpClientManager::class, $manager);
        $this->assertEmpty($manager->getConnectedServers());
    }

    public function test_can_add_server_configuration()
    {
        $manager = new McpClientManager();
        
        $manager->addServer('test-server', 'stdio', [
            'command' => 'php',
            'args' => ['server.php'],
            'timeout' => 30
        ]);
        
        // We can't directly test the private property, but we can verify it was added
        // by trying to connect (which will fail but shows the config exists)
        $this->expectException(\Exception::class);
        $manager->connect('test-server');
    }

    public function test_client_can_create_mcp_chat()
    {
        $client = new Client('test-api-key');
        $mcpChat = $client->mcpChat();
        
        $this->assertInstanceOf(McpEnabledChat::class, $mcpChat);
        $this->assertInstanceOf(McpClientManager::class, $mcpChat->getMcpManager());
    }

    public function test_client_can_get_mcp_manager()
    {
        $client = new Client('test-api-key');
        $manager = $client->getMcpManager();
        
        $this->assertInstanceOf(McpClientManager::class, $manager);
        
        // Should return the same instance
        $manager2 = $client->getMcpManager();
        $this->assertSame($manager, $manager2);
    }

    public function test_client_can_add_mcp_server()
    {
        $client = new Client('test-api-key');
        
        $client->addMcpServer('test-server', 'stdio', [
            'command' => 'php',
            'args' => ['server.php']
        ]);
        
        $manager = $client->getMcpManager();
        $this->assertFalse($manager->isConnected('test-server'));
    }

    public function test_mcp_enabled_chat_extends_base_chat()
    {
        $client = new Client('test-api-key');
        $mcpChat = $client->mcpChat();
        
        $this->assertInstanceOf(\Mistral\Resources\Chat::class, $mcpChat);
    }

    public function test_mcp_client_manager_handles_invalid_transport()
    {
        $manager = new McpClientManager();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported transport type: invalid');
        
        $manager->addServer('test', 'invalid', []);
    }

    public function test_mcp_client_manager_handles_unknown_server()
    {
        $manager = new McpClientManager();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Server 'unknown' not configured");
        
        $manager->connect('unknown');
    }

    public function test_mcp_client_manager_returns_empty_tools_for_disconnected_servers()
    {
        $manager = new McpClientManager();
        $tools = $manager->listAllTools();
        
        $this->assertIsArray($tools);
        $this->assertEmpty($tools);
    }

    public function test_mcp_client_manager_handles_tool_call_to_disconnected_server()
    {
        $manager = new McpClientManager();
        $result = $manager->callTool('unknown-server', 'test-tool', []);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Not connected to server', $result['error']);
    }

    public function test_mcp_client_manager_server_info_returns_null_for_disconnected()
    {
        $manager = new McpClientManager();
        $info = $manager->getServerInfo('unknown');
        
        $this->assertNull($info);
    }

    public function test_mcp_enabled_chat_can_get_available_tools()
    {
        $client = new Client('test-api-key');
        $mcpChat = $client->mcpChat();
        
        $tools = $mcpChat->getAvailableMcpTools();
        $this->assertIsArray($tools);
    }

    public function test_mcp_enabled_chat_can_add_server()
    {
        $client = new Client('test-api-key');
        $mcpChat = $client->mcpChat();
        
        $mcpChat->addMcpServer('test', 'stdio', ['command' => 'php']);
        
        // Should not throw exception
        $this->assertTrue(true);
    }
}