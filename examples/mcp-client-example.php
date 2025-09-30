<?php

/**
 * Example: Mistral AI with MCP Client Integration
 * 
 * This example demonstrates how to use Mistral AI with external MCP tools
 * to extend the AI's capabilities with external services.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mistral\Client;
use Psr\Log\NullLogger;

// Initialize Mistral client
$client = new Client('your-mistral-api-key');

// Example 1: Add a file management MCP server (stdio transport)
$client->addMcpServer('file-tools', 'stdio', [
    'command' => 'python',
    'args' => ['file_server.py'],
    'working_dir' => __DIR__ . '/mcp-servers',
    'timeout' => 30,
    'env' => [
        'ALLOWED_PATHS' => '/tmp,/var/data'
    ]
]);

// Example 2: Add a web API MCP server (HTTP transport)
$client->addMcpServer('weather-api', 'http', [
    'url' => 'http://localhost:8080',
    'timeout' => 15,
    'headers' => [
        'Authorization' => 'Bearer your-api-token'
    ]
]);

// Get MCP-enabled chat
$mcpChat = $client->mcpChat(new NullLogger());

try {
    echo "=== Mistral AI with MCP Tools Example ===\n\n";
    
    // Connect to MCP servers
    echo "Connecting to MCP servers...\n";
    
    try {
        $mcpChat->connectToMcpServer('file-tools');
        echo "✓ Connected to file-tools server\n";
    } catch (Exception $e) {
        echo "⚠ Could not connect to file-tools: " . $e->getMessage() . "\n";
        echo "  (This is expected without a real MCP server)\n";
    }
    
    // Note: This would fail without a real server, but shows the pattern
    try {
        $mcpChat->connectToMcpServer('weather-api');
        echo "✓ Connected to weather-api server\n";
    } catch (Exception $e) {
        echo "⚠ Could not connect to weather-api: " . $e->getMessage() . "\n";
        echo "  (This is expected without a real MCP server)\n";
    }
    
    // List available tools
    echo "\nAvailable MCP tools:\n";
    $tools = $mcpChat->getAvailableMcpTools();
    foreach ($tools as $serverName => $serverTools) {
        echo "Server '{$serverName}':\n";
        if (empty($serverTools)) {
            echo "  (No tools available - server may not be running)\n";
        } else {
            foreach ($serverTools as $tool) {
                echo "  - {$tool['name']}: {$tool['description']}\n";
            }
        }
    }
    
    // Example conversation with tool usage
    echo "\n=== Example Conversation ===\n";
    
    $messages = [
        [
            'role' => 'user', 
            'content' => 'Can you help me analyze a log file? First, list the files in /tmp to see what\'s available.'
        ]
    ];
    
    echo "User: " . $messages[0]['content'] . "\n\n";
    
    // Note: This would require a real MCP server to work
    echo "Assistant: I'd be happy to help you analyze a log file! However, to demonstrate this properly, you would need:\n\n";
    echo "1. A running MCP server that provides file system tools\n";
    echo "2. Your actual Mistral API key\n";
    echo "3. The MCP server configured to allow access to the desired directories\n\n";
    echo "Here's what would happen:\n";
    echo "- Mistral would automatically detect available file system tools from the MCP server\n";
    echo "- It would call the 'list_files' tool with the path '/tmp'\n";
    echo "- Get the results and show you available files\n";
    echo "- Then help you analyze any log files it finds\n\n";
    
    // Manual tool demonstration (if you had a working server)
    echo "=== Manual Tool Call Example ===\n";
    $mcpManager = $client->getMcpManager();
    
    $result = $mcpManager->callTool('file-tools', 'list_files', ['path' => '/tmp']);
    if (!$result['success']) {
        echo "Tool call result: " . $result['error'] . "\n";
        echo "(This is expected without a running MCP server)\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "This is expected when running without real MCP servers.\n";
} finally {
    // Clean up connections
    echo "\nCleaning up MCP connections...\n";
    $mcpManager = $client->getMcpManager();
    $mcpManager->disconnectAll();
    echo "✓ Disconnected from all MCP servers\n";
}

echo "\n=== Setting Up Real MCP Servers ===\n";
echo "To use this with real MCP servers:\n\n";
echo "1. Create or find MCP server implementations\n";
echo "2. For stdio servers: Ensure the command and args point to executable servers\n";
echo "3. For HTTP servers: Ensure the URL points to running MCP HTTP servers\n";
echo "4. Set your real Mistral API key\n";
echo "5. Configure appropriate permissions and security settings\n\n";
echo "Example MCP servers you can use:\n";
echo "- File system tools: https://github.com/modelcontextprotocol/servers\n";
echo "- Database tools: Custom implementations\n";
echo "- Web API wrappers: Custom HTTP MCP servers\n";
echo "- Calculator/utility tools: Simple stdio implementations\n\n";