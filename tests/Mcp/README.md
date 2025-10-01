# MCP Test Infrastructure

This directory contains MCP (Model Context Protocol) test infrastructure for the Mistral PHP SDK.

## Files

### `McpIntegrationTest.php`

Integration tests that validate MCP functionality by connecting to the official `@modelcontextprotocol/server-everything` via npx. These tests:
- Connect to the everything server via stdio transport
- Validate connection lifecycle (connect/disconnect)
- Test multiple server connections
- Test server connection state management

**Note**: Due to a known issue with `logiscape/mcp-sdk-php` v1.2.3, tests that involve listing or calling tools are currently skipped. The SDK has a TypeError with `LoggingMessageNotificationParams` from the everything server. Tests that only connect/disconnect work correctly.

### `McpClientTest.php`

Unit and integration tests for the MCP client manager and related components.

### `test_everything_server.php`

Example script demonstrating the everything server and documenting the SDK notification compatibility issue.

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

## About the Everything Server

The `@modelcontextprotocol/server-everything` is a comprehensive MCP reference implementation that exercises all protocol features. It's accessed via npx without requiring global installation:

```bash
npx @modelcontextprotocol/server-everything stdio
```

### SDK Compatibility Note

The current version of `logiscape/mcp-sdk-php` (v1.2.3) has a known issue with notification handling from the everything server:

```
TypeError: Notification::__construct(): Argument #2 ($params) must be of type 
?NotificationParams, LoggingMessageNotificationParams given
```

This affects operations that trigger server notifications (like listing tools). Tests involving these operations are marked as skipped until the SDK is updated. Connection and disconnection tests work correctly and validate the basic MCP protocol functionality.

## Example Usage

You can manually test the everything server:

```php
<?php
require_once 'vendor/autoload.php';

use Nicobleiler\Mistral\Mcp\McpClientManager;

$manager = new McpClientManager();
$manager->addServer('everything', 'stdio', [
    'command' => 'npx',
    'args' => ['@modelcontextprotocol/server-everything', 'stdio'],
]);

$manager->connect('everything');
echo "Connected: " . ($manager->isConnected('everything') ? 'Yes' : 'No') . "\n";

$manager->disconnect('everything');
```

This validates that the Mistral PHP SDK's MCP integration can successfully connect to and communicate with official MCP servers.
