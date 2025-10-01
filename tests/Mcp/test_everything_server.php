#!/usr/bin/env php
<?php
/**
 * Example: Testing with the MCP Everything Server
 * 
 * This example shows how to interact with the @modelcontextprotocol/server-everything
 * using npx (no global installation required).
 * 
 * Note: Due to a bug in the PHP MCP SDK (v1.2.3) with notification handling,
 * connection attempts will fail with a TypeError. This script demonstrates the issue.
 * 
 * Prerequisites:
 * - Node.js and npx installed
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Nicobleiler\Mistral\Mcp\McpClientManager;
use Psr\Log\NullLogger;

echo "=== MCP Everything Server Test ===\n\n";
echo "Note: The PHP MCP SDK has a known issue with notification handling.\n";
echo "This test demonstrates the issue when connecting to server-everything.\n\n";

$manager = new McpClientManager(new NullLogger());

// Check if npx is available
$checkCommand = 'which npx 2>&1';
exec($checkCommand, $output, $returnCode);

if ($returnCode !== 0) {
    echo "ERROR: npx not found.\n";
    echo "Install Node.js which includes npx: https://nodejs.org/\n";
    exit(1);
}

echo "✓ Found npx at: " . trim($output[0]) . "\n\n";

// Add the everything server using npx
$manager->addServer('everything', 'stdio', [
    'command' => 'npx',
    'args' => ['--yes', '@modelcontextprotocol/server-everything', 'stdio'],
]);

echo "Connecting to MCP everything server via npx...\n";

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
        echo "This is the known SDK bug with server-everything.\n";
        echo "The everything server sends logging notifications that cause type errors\n";
        echo "in the current version of logiscape/mcp-sdk-php (v1.2.3).\n\n";
        echo "Server-everything sends: LoggingMessageNotificationParams\n";
        echo "SDK expects: NotificationParams\n";
    }

    echo "\nDisconnecting...\n";
    $manager->disconnect('everything');
    echo "✓ Disconnected\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Summary ===\n";
echo "The @modelcontextprotocol/server-everything is accessible via npx.\n";
echo "However, the current PHP MCP SDK (logiscape/mcp-sdk-php v1.2.3) has\n";
echo "incompatibilities with the notification system used by server-everything.\n\n";
echo "The integration tests in McpIntegrationTest.php document the expected\n";
echo "behavior once the SDK is updated to handle these notifications correctly.\n";
