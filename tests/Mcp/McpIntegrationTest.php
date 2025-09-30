<?php

namespace Mistral\Tests\Mcp;

use PHPUnit\Framework\TestCase;
use Mistral\Mcp\McpClientManager;
use Psr\Log\NullLogger;

/**
 * Integration tests for MCP using a real MCP test server
 * 
 * These tests validate actual MCP protocol functionality by connecting to
 * a simple MCP server that implements the protocol correctly.
 */
class McpIntegrationTest extends TestCase
{
    private McpClientManager $manager;
    private string $serverPath;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new McpClientManager(new NullLogger());
        $this->serverPath = __DIR__ . '/simple_mcp_server.php';
        
        // Ensure the test server exists
        if (!file_exists($this->serverPath)) {
            $this->markTestSkipped('MCP test server not found');
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up any connections
        $this->manager->disconnectAll();
        parent::tearDown();
    }
    
    public function test_can_connect_to_mcp_server()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        
        $this->assertTrue($this->manager->isConnected('test'));
        $this->assertContains('test', $this->manager->getConnectedServers());
    }
    
    public function test_can_list_tools_from_connected_server()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        
        $tools = $this->manager->listAllTools();
        
        $this->assertIsArray($tools);
        $this->assertArrayHasKey('test', $tools);
        $this->assertNotEmpty($tools['test']);
        
        // Check that we have the expected tools
        $toolNames = array_column($tools['test'], 'name');
        $this->assertContains('echo', $toolNames);
        $this->assertContains('add', $toolNames);
        $this->assertContains('get_info', $toolNames);
        
        // Verify tool structure
        foreach ($tools['test'] as $tool) {
            $this->assertArrayHasKey('name', $tool);
            $this->assertArrayHasKey('description', $tool);
            $this->assertArrayHasKey('inputSchema', $tool);
        }
    }
    
    public function test_can_call_echo_tool()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        
        $result = $this->manager->callTool('test', 'echo', [
            'message' => 'Hello, MCP!'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Hello, MCP!', $result['content']);
        $this->assertEmpty($result['error'] ?? '');
    }
    
    public function test_can_call_add_tool()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        
        $result = $this->manager->callTool('test', 'add', [
            'a' => 5,
            'b' => 3
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('8', $result['content']);
    }
    
    public function test_can_call_get_info_tool()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        
        $result = $this->manager->callTool('test', 'get_info', []);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Simple MCP Test Server', $result['content']);
    }
    
    public function test_handles_invalid_tool_name()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        
        $result = $this->manager->callTool('test', 'nonexistent_tool', []);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
    
    public function test_can_disconnect_from_server()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test');
        $this->assertTrue($this->manager->isConnected('test'));
        
        $this->manager->disconnect('test');
        $this->assertFalse($this->manager->isConnected('test'));
    }
    
    public function test_can_connect_to_multiple_servers()
    {
        // Add two instances of the test server
        $this->manager->addServer('test1', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->addServer('test2', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test1');
        $this->manager->connect('test2');
        
        $this->assertTrue($this->manager->isConnected('test1'));
        $this->assertTrue($this->manager->isConnected('test2'));
        
        $connectedServers = $this->manager->getConnectedServers();
        $this->assertCount(2, $connectedServers);
        $this->assertContains('test1', $connectedServers);
        $this->assertContains('test2', $connectedServers);
    }
    
    public function test_can_list_tools_from_multiple_servers()
    {
        $this->manager->addServer('test1', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->addServer('test2', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test1');
        $this->manager->connect('test2');
        
        $tools = $this->manager->listAllTools();
        
        $this->assertArrayHasKey('test1', $tools);
        $this->assertArrayHasKey('test2', $tools);
        $this->assertNotEmpty($tools['test1']);
        $this->assertNotEmpty($tools['test2']);
    }
    
    public function test_disconnect_all_closes_all_connections()
    {
        $this->manager->addServer('test1', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->addServer('test2', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $this->manager->connect('test1');
        $this->manager->connect('test2');
        
        $this->manager->disconnectAll();
        
        $this->assertFalse($this->manager->isConnected('test1'));
        $this->assertFalse($this->manager->isConnected('test2'));
        $this->assertEmpty($this->manager->getConnectedServers());
    }
    
    public function test_server_info_reflects_connection_status()
    {
        $this->manager->addServer('test', 'stdio', [
            'command' => 'php',
            'args' => [$this->serverPath],
        ]);
        
        $info = $this->manager->getServerInfo('test');
        $this->assertFalse($info['connected']);
        
        $this->manager->connect('test');
        
        $info = $this->manager->getServerInfo('test');
        $this->assertTrue($info['connected']);
        
        $this->manager->disconnect('test');
        
        $info = $this->manager->getServerInfo('test');
        $this->assertFalse($info['connected']);
    }
}
