# MCP Test Server

This directory contains MCP (Model Context Protocol) test infrastructure for the Mistral PHP SDK.

## Files

### `simple_mcp_server.php`

A minimal MCP server implementation for integration testing. This server:
- Implements the MCP protocol specification
- Provides three test tools: `echo`, `add`, and `get_info`
- Does not emit automatic notifications (avoiding PHP SDK compatibility issues)
- Runs via stdio transport for reliable testing

**Tools:**
- `echo` - Echoes back a message
- `add` - Adds two numbers
- `get_info` - Returns server information

### `McpIntegrationTest.php`

Comprehensive integration tests that validate MCP functionality by connecting to a real MCP server. These tests:
- Connect to the simple_mcp_server.php via stdio
- List available tools from the server
- Call tools and verify responses
- Test connection lifecycle (connect/disconnect)
- Test multiple server connections

### `McpClientTest.php`

Unit and integration tests for the MCP client manager and related components.

## Running the Tests

Run all MCP tests:
```bash
vendor/bin/phpunit tests/Mcp/
```

Run only integration tests:
```bash
vendor/bin/phpunit tests/Mcp/McpIntegrationTest.php
```

Run only unit tests:
```bash
vendor/bin/phpunit tests/Mcp/McpClientTest.php
```

## Using the Test Server Manually

You can use the simple MCP server for manual testing:

```php
<?php
require_once 'vendor/autoload.php';

use Mistral\Mcp\McpClientManager;

$manager = new McpClientManager();
$manager->addServer('test', 'stdio', [
    'command' => 'php',
    'args' => [__DIR__ . '/tests/Mcp/simple_mcp_server.php'],
]);

$manager->connect('test');

// List tools
$tools = $manager->listAllTools();
print_r($tools);

// Call a tool
$result = $manager->callTool('test', 'add', ['a' => 5, 'b' => 3]);
echo $result['content']; // Output: Result: 8

$manager->disconnect('test');
```

## About the Everything Server

The `@modelcontextprotocol/server-everything` npm package is a comprehensive MCP reference implementation that exercises all protocol features. While we've installed it (`npm install -g @modelcontextprotocol/server-everything`), we use the simpler test server for most tests due to compatibility issues between the everything server's notification system and the current PHP MCP SDK.

The simple test server provides sufficient coverage for validating:
- Protocol handshake (initialize)
- Tool listing
- Tool calling with various argument types
- Connection lifecycle
- Multiple concurrent server connections

This ensures the Mistral PHP SDK's MCP integration works correctly with real MCP servers.
