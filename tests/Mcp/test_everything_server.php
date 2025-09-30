#!/usr/bin/env php
<?php
/**
 * Example: Testing with the MCP Everything Server
 * 
 * This example shows how to interact with the @modelcontextprotocol/server-everything
 * Note: Due to a bug in the PHP MCP SDK (v1.2.3) with notification handling,
 * this may encounter errors. Use the simple_mcp_server.php for reliable testing.
 * 
 * Prerequisites:
 * - npm install -g @modelcontextprotocol/server-everything
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Mistral\Mcp\McpClientManager;
use Psr\Log\NullLogger;

echo "=== MCP Everything Server Test ===\n\n";
echo "Note: The PHP MCP SDK has a known issue with notification handling.\n";
echo "This test demonstrates the issue and why we use simple_mcp_server.php instead.\n\n";

$manager = new McpClientManager(new NullLogger());

// Check if mcp-server-everything is available
$checkCommand = 'which mcp-server-everything 2>&1';
exec($checkCommand, $output, $returnCode);

if ($returnCode !== 0) {
    echo "ERROR: mcp-server-everything not found.\n";
    echo "Install it with: npm install -g @modelcontextprotocol/server-everything\n";
    exit(1);
}

echo "✓ Found mcp-server-everything at: " . trim($output[0]) . "\n\n";

// Add the everything server
$manager->addServer('everything', 'stdio', [
    'command' => 'mcp-server-everything',
    'args' => ['stdio'],
]);

echo "Connecting to MCP everything server...\n";

try {
    $manager->connect('everything');
    echo "✓ Connected successfully\n\n";
    
    echo "Attempting to list tools...\n";
    
    try {
        $tools = $manager->listAllTools();
        
        echo "✓ Listed tools successfully!\n";
        foreach ($tools as $serverName => $serverTools) {
            echo "Server: {$serverName}\n";
            echo "Tools count: " . count($serverTools) . "\n";
            foreach ($serverTools as $tool) {
                echo "  - {$tool['name']}: {$tool['description']}\n";
            }
        }
    } catch (\TypeError $e) {
        echo "\n✗ EXPECTED ERROR: Notification type incompatibility in PHP MCP SDK\n";
        echo "Error: " . $e->getMessage() . "\n\n";
        echo "This is why we use simple_mcp_server.php for testing instead.\n";
        echo "The everything server sends logging notifications that cause type errors\n";
        echo "in the current version of logiscape/mcp-sdk-php (v1.2.3).\n\n";
        echo "Our simple_mcp_server.php avoids this by not sending notifications.\n";
    }
    
    echo "\nDisconnecting...\n";
    $manager->disconnect('everything');
    echo "✓ Disconnected\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Recommendation ===\n";
echo "For reliable MCP testing, use tests/Mcp/simple_mcp_server.php\n";
echo "See tests/Mcp/README.md for more information.\n";
