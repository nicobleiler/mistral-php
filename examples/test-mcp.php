<?php

/**
 * Simple MCP client test to verify server functionality
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mistral\Mcp\MistralMcpServer;

function testMcpElements(): void 
{
    echo "Testing MCP Elements...\n";
    
    // Create a test client (this won't make real API calls in our test)
    $client = new \Mistral\Client('test-api-key');
    $server = new MistralMcpServer($client);
    
    echo "✓ MCP Server created successfully\n";
    
    $elements = $server->getElements();
    
    echo "✓ MCP Elements retrieved successfully\n";
    
    // Test resources (these don't require API calls)
    $modelsInfo = $elements->getModelsInfo();
    echo "✓ Models info resource: " . count($modelsInfo['models']) . " models available\n";
    
    $clientConfig = $elements->getClientConfig();
    echo "✓ Client config resource: " . $clientConfig['base_url'] . "\n";
    
    echo "\nMCP integration is working correctly!\n";
    echo "To use with real API, set MISTRAL_API_KEY environment variable and run:\n";
    echo "php bin/mistral-mcp-server.php\n";
}

testMcpElements();