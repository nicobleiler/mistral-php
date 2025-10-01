<?php

namespace Nicobleiler\Mistral\Mcp;

use Mcp\Client\Client;
use Mcp\Client\ClientSession;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * MCP Client Manager for Mistral using the logiscape/mcp-sdk-php
 * 
 * Manages connections to MCP servers and provides tool calling functionality.
 */
class McpClientManager
{
    /** @var array<string, ClientSession> */
    private array $sessions = [];

    /** @var array<string, array> */
    private array $serverConfigs = [];

    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Add an MCP server configuration
     *
     * @param string $name Server identifier
     * @param string $transport Transport type ('stdio' or 'http')
     * @param array $config Configuration options
     */
    public function addServer(string $name, string $transport, array $config): void
    {
        if (!in_array($transport, ['stdio', 'http'])) {
            throw new \InvalidArgumentException("Unsupported transport type: {$transport}");
        }

        $this->serverConfigs[$name] = [
            'transport' => $transport,
            'config' => $config
        ];

        $this->logger->info("Added MCP server configuration '{$name}' with transport '{$transport}'");
    }

    /**
     * Connect to an MCP server
     *
     * @param string $serverName
     * @throws \Exception
     */
    public function connect(string $serverName): void
    {
        if (!isset($this->serverConfigs[$serverName])) {
            throw new \InvalidArgumentException("Server '{$serverName}' not configured");
        }

        if (isset($this->sessions[$serverName])) {
            return; // Already connected
        }

        $config = $this->serverConfigs[$serverName];
        $this->logger->info("Attempting to connect to MCP server '{$serverName}'");

        try {
            $client = new Client($this->logger);

            if ($config['transport'] === 'stdio') {
                // STDIO transport
                $command = $config['config']['command'] ?? 'node';
                $args = $config['config']['args'] ?? [];
                $env = $config['config']['env'] ?? null;

                $session = $client->connect($command, $args, $env);
            } else {
                // HTTP transport
                $url = $config['config']['url'] ?? '';
                $headers = $config['config']['headers'] ?? [];
                $httpOptions = [
                    'connectionTimeout' => $config['config']['timeout'] ?? 30,
                    'readTimeout' => $config['config']['timeout'] ?? 60,
                ];

                $session = $client->connect($url, $headers, $httpOptions);
            }

            $this->sessions[$serverName] = $session;
            $this->logger->info("Connected to MCP server '{$serverName}'");
        } catch (\Exception $e) {
            $this->logger->error("Failed to connect to MCP server '{$serverName}'", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Disconnect from an MCP server
     *
     * @param string $serverName
     */
    public function disconnect(string $serverName): void
    {
        if (isset($this->sessions[$serverName])) {
            try {
                // The logiscape SDK doesn't have an explicit disconnect method
                // The session will be closed when the object is destroyed
                unset($this->sessions[$serverName]);
                $this->logger->info("Disconnected from MCP server '{$serverName}'");
            } catch (\Exception $e) {
                $this->logger->warning("Error disconnecting from MCP server '{$serverName}'", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Disconnect from all MCP servers
     */
    public function disconnectAll(): void
    {
        foreach (array_keys($this->sessions) as $serverName) {
            $this->disconnect($serverName);
        }
    }

    /**
     * Get list of available tools from all connected servers
     *
     * @return array<string, array> Server name => tools array
     */
    public function listAllTools(): array
    {
        $allTools = [];

        foreach ($this->sessions as $serverName => $session) {
            try {
                $toolsResult = $session->listTools();

                $tools = [];
                if (!empty($toolsResult->tools)) {
                    foreach ($toolsResult->tools as $tool) {
                        $tools[] = [
                            'name' => $tool->name,
                            'description' => $tool->description ?? '',
                            'inputSchema' => $tool->inputSchema ?? ['type' => 'object', 'properties' => []],
                        ];
                    }
                }

                $allTools[$serverName] = $tools;
            } catch (\Exception $e) {
                $this->logger->warning("Failed to list tools from server '{$serverName}'", [
                    'error' => $e->getMessage(),
                ]);
                $allTools[$serverName] = [];
            }
        }

        return $allTools;
    }

    /**
     * Call a tool on a specific MCP server
     *
     * @param string $serverName
     * @param string $toolName
     * @param array $arguments
     * @return array{success: bool, content: string, error?: string}
     */
    public function callTool(string $serverName, string $toolName, array $arguments = []): array
    {
        if (!isset($this->sessions[$serverName])) {
            return [
                'success' => false,
                'content' => '',
                'error' => "Not connected to server '{$serverName}'"
            ];
        }

        try {
            $this->logger->info("Calling tool '{$toolName}' on server '{$serverName}'", [
                'arguments' => $arguments
            ]);

            $session = $this->sessions[$serverName];
            $result = $session->callTool($toolName, $arguments);

            // Extract content from the result
            $content = '';
            if (!empty($result->content)) {
                foreach ($result->content as $contentItem) {
                    if (isset($contentItem->text)) {
                        $content .= $contentItem->text;
                    }
                }
            }

            return [
                'success' => !$result->isError,
                'content' => $content,
                'error' => $result->isError ? 'Tool execution failed' : null
            ];
        } catch (\Exception $e) {
            $this->logger->error("Tool call failed", [
                'server' => $serverName,
                'tool' => $toolName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'content' => '',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get connected servers
     *
     * @return array<string>
     */
    public function getConnectedServers(): array
    {
        return array_keys($this->sessions);
    }

    /**
     * Check if a server is connected
     *
     * @param string $serverName
     * @return bool
     */
    public function isConnected(string $serverName): bool
    {
        return isset($this->sessions[$serverName]);
    }

    /**
     * Get server information
     *
     * @param string $serverName
     * @return array{name: string, transport: string, connected: bool}|null
     */
    public function getServerInfo(string $serverName): ?array
    {
        if (!isset($this->serverConfigs[$serverName])) {
            return null;
        }

        return [
            'name' => $serverName,
            'transport' => $this->serverConfigs[$serverName]['transport'],
            'connected' => $this->isConnected($serverName)
        ];
    }
}
