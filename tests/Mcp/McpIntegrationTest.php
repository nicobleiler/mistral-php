<?php

namespace Nicobleiler\Mistral\Tests\Mcp;

use PHPUnit\Framework\TestCase;
use Nicobleiler\Mistral\Mcp\McpClientManager;
use Psr\Log\NullLogger;

/**
 * Integration tests for MCP using the @modelcontextprotocol/server-everything
 * 
 * These tests validate MCP protocol functionality using the official MCP everything server via npx.
 * 
 * IMPORTANT: Due to a known incompatibility between logiscape/mcp-sdk-php v1.2.3 and
 * the everything server's notification system, most tests are currently skipped.
 * The SDK has a TypeError: "Notification::__construct(): Argument #2 ($params) must be 
 * of type ?NotificationParams, LoggingMessageNotificationParams given"
 * 
 * These tests document the expected behavior once the SDK issue is resolved.
 */
class McpIntegrationTest extends TestCase
{
    private McpClientManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new McpClientManager(new NullLogger());

        // Check if npx is available
        exec('which npx 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            $this->markTestSkipped('npx is not available');
        }
    }

    protected function tearDown(): void
    {
        // Clean up any connections
        try {
            $this->manager->disconnectAll();
        } catch (\Exception $e) {
            // Ignore disconnection errors in teardown
        }
        parent::tearDown();
    }

    public function test_can_configure_mcp_everything_server()
    {
        // This test validates that we can configure the everything server
        // without actually connecting (which would trigger the SDK bug)
        $this->manager->addServer('everything', 'stdio', [
            'command' => 'npx',
            'args' => ['--yes', '@modelcontextprotocol/server-everything', 'stdio'],
        ]);

        $info = $this->manager->getServerInfo('everything');
        $this->assertNotNull($info);
        $this->assertEquals('everything', $info['name']);
        $this->assertEquals('stdio', $info['transport']);
        $this->assertFalse($info['connected']);
    }

    public function test_demonstrates_sdk_notification_bug()
    {
        $this->markTestSkipped(
            'This test is skipped by default as it demonstrates the SDK bug. ' .
                'The everything server sends LoggingMessageNotification which causes: ' .
                'TypeError: Notification::__construct(): Argument #2 ($params) must be of type ' .
                '?NotificationParams, LoggingMessageNotificationParams given. ' .
                'This affects logiscape/mcp-sdk-php v1.2.3 when connecting to server-everything.'
        );

        $this->manager->addServer('everything', 'stdio', [
            'command' => 'npx',
            'args' => ['--yes', '@modelcontextprotocol/server-everything', 'stdio'],
        ]);

        // This will trigger the SDK bug
        $this->expectException(\TypeError::class);
        $this->manager->connect('everything');
    }

    public function test_server_configuration_with_npx()
    {
        // Validate that npx configuration is properly stored
        $this->manager->addServer('everything', 'stdio', [
            'command' => 'npx',
            'args' => ['--yes', '@modelcontextprotocol/server-everything', 'stdio'],
        ]);

        $info = $this->manager->getServerInfo('everything');
        $this->assertEquals('stdio', $info['transport']);
    }

    public function test_multiple_server_configuration()
    {
        // Test that multiple everything servers can be configured
        $this->manager->addServer('everything1', 'stdio', [
            'command' => 'npx',
            'args' => ['--yes', '@modelcontextprotocol/server-everything', 'stdio'],
        ]);

        $this->manager->addServer('everything2', 'stdio', [
            'command' => 'npx',
            'args' => ['--yes', '@modelcontextprotocol/server-everything', 'stdio'],
        ]);

        $info1 = $this->manager->getServerInfo('everything1');
        $info2 = $this->manager->getServerInfo('everything2');

        $this->assertNotNull($info1);
        $this->assertNotNull($info2);
        $this->assertEquals('everything1', $info1['name']);
        $this->assertEquals('everything2', $info2['name']);
    }
}
