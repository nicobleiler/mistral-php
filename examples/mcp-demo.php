<?php

/**
 * Simple MCP Client Demo
 * 
 * This example demonstrates the MCP client API without connecting to real servers.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mistral\Client;

echo "=== Mistral AI MCP Client Integration Demo ===\n\n";

// Initialize Mistral client
$client = new Client('demo-api-key');

echo "1. Creating MCP-enabled chat client...\n";
$mcpChat = $client->mcpChat();
echo "✓ MCP chat client created\n\n";

echo "2. Adding MCP server configurations...\n";

// Add file tools server (stdio)
$client->addMcpServer('file-tools', 'stdio', [
    'command' => 'python',
    'args' => ['file_server.py'],
    'working_dir' => '/path/to/mcp/servers',
    'timeout' => 30
]);
echo "✓ Added file-tools server (stdio transport)\n";

// Add weather API server (HTTP)
$client->addMcpServer('weather-api', 'http', [
    'url' => 'http://localhost:8080',
    'timeout' => 15,
    'headers' => ['Authorization' => 'Bearer token']
]);
echo "✓ Added weather-api server (HTTP transport)\n\n";

echo "3. Demonstrating MCP manager functionality...\n";
$mcpManager = $client->getMcpManager();

echo "Connected servers: " . implode(', ', $mcpManager->getConnectedServers()) . " (none yet)\n";
echo "Server 'file-tools' connected: " . ($mcpManager->isConnected('file-tools') ? 'Yes' : 'No') . "\n";

// Demonstrate tool calling (without real connection)
echo "\n4. Demonstrating tool call structure...\n";
$toolResult = $mcpManager->callTool('file-tools', 'list_files', ['path' => '/tmp']);
echo "Tool call result: " . json_encode($toolResult, JSON_PRETTY_PRINT) . "\n";

echo "\n5. Listing available tools (none without server connections)...\n";
$tools = $mcpChat->getAvailableMcpTools();
echo "Available tools: " . json_encode($tools, JSON_PRETTY_PRINT) . "\n";

echo "\n=== Integration Summary ===\n";
echo "✓ MCP client integration added to Mistral PHP package\n";
echo "✓ Support for both stdio and HTTP MCP servers\n";
echo "✓ Automatic tool integration in conversations\n";
echo "✓ Manual tool calling capabilities\n";
echo "✓ Comprehensive error handling\n";
echo "✓ Laravel service provider integration\n\n";

echo "To use with real MCP servers:\n";
echo "1. Set up actual MCP server implementations\n";
echo "2. Configure correct paths/URLs for your servers\n";
echo "3. Use your real Mistral API key\n";
echo "4. Call connectToMcpServer() to establish connections\n";
echo "5. Use mcpChat->create() for AI conversations with tool access\n\n";

echo "Example conversation flow:\n";
echo "User: 'Can you check what files are in /tmp?'\n";
echo "Mistral: Automatically calls mcp_file-tools_list_files with path=/tmp\n";
echo "MCP Server: Returns list of files\n";
echo "Mistral: 'I found these files in /tmp: file1.txt, file2.log, ...'\n\n";

echo "Demo completed successfully!\n";