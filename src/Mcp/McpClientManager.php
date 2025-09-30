<?php

namespace Mistral\Mcp;

use PhpMcp\Client\Client;
use PhpMcp\Client\ClientBuilder;
use PhpMcp\Client\Enum\TransportType;
use PhpMcp\Client\Exception\McpClientException;
use PhpMcp\Client\Model\Capabilities as ClientCapabilities;
use PhpMcp\Client\ServerConfig;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * MCP Client Manager for Mistral
 * 
 * Manages connections to external MCP servers and provides
 * tools for integration with Mistral conversations.
 */
class McpClientManager
{
    /** @var array<string, Client> */
    private array $clients = [];
    
    /** @var array<string, ServerConfig> */
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
        $transportType = match (strtolower($transport)) {
            'stdio' => TransportType::Stdio,
            'http' => TransportType::Http,
            default => throw new \InvalidArgumentException("Unsupported transport type: {$transport}")
        };

        $serverConfig = new ServerConfig(
            name: $name,
            transport: $transportType,
            timeout: $config['timeout'] ?? 30,
            command: $config['command'] ?? null,
            args: $config['args'] ?? [],
            workingDir: $config['working_dir'] ?? null,
            url: $config['url'] ?? null,
            env: $config['env'] ?? null,
            headers: $config['headers'] ?? null
        );

        $this->serverConfigs[$name] = $serverConfig;
    }

    /**
     * Connect to an MCP server
     *
     * @param string $serverName
     * @throws McpClientException
     */
    public function connect(string $serverName): void
    {
        if (!isset($this->serverConfigs[$serverName])) {
            throw new \InvalidArgumentException("Server '{$serverName}' not configured");
        }

        if (isset($this->clients[$serverName])) {
            return; // Already connected
        }

        $config = $this->serverConfigs[$serverName];
        
        $client = Client::make()
            ->withClientInfo('Mistral PHP Client', '1.0.0')
            ->withCapabilities(ClientCapabilities::forClient(supportsSampling: false))
            ->withLogger($this->logger)
            ->withServerConfig($config)
            ->build();

        try {
            $client->initialize();
            $this->clients[$serverName] = $client;
            
            $this->logger->info("Connected to MCP server '{$serverName}'", [
                'server_name' => $client->getServerName(),
                'server_version' => $client->getServerVersion(),
                'protocol_version' => $client->getNegotiatedProtocolVersion(),
            ]);
        } catch (McpClientException $e) {
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
        if (isset($this->clients[$serverName])) {
            $this->clients[$serverName]->disconnect();
            unset($this->clients[$serverName]);
            $this->logger->info("Disconnected from MCP server '{$serverName}'");
        }
    }

    /**
     * Disconnect from all MCP servers
     */
    public function disconnectAll(): void
    {
        foreach (array_keys($this->clients) as $serverName) {
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
        
        foreach ($this->clients as $serverName => $client) {
            try {
                $tools = $client->listTools();
                $allTools[$serverName] = array_map(function ($tool) {
                    return [
                        'name' => $tool->name,
                        'description' => $tool->description ?? '',
                        'inputSchema' => $tool->inputSchema ?? [],
                    ];
                }, $tools);
            } catch (McpClientException $e) {
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
        if (!isset($this->clients[$serverName])) {
            return [
                'success' => false,
                'content' => '',
                'error' => "Not connected to server '{$serverName}'"
            ];
        }

        try {
            $client = $this->clients[$serverName];
            $result = $client->callTool($toolName, $arguments);

            if ($result->isSuccess()) {
                $content = '';
                foreach ($result->content as $contentItem) {
                    if (method_exists($contentItem, 'getText')) {
                        $content .= $contentItem->getText();
                    } elseif (isset($contentItem->text)) {
                        $content .= $contentItem->text;
                    }
                }

                return [
                    'success' => true,
                    'content' => $content,
                ];
            } else {
                $errorContent = '';
                foreach ($result->content as $contentItem) {
                    if (method_exists($contentItem, 'getText')) {
                        $errorContent .= $contentItem->getText();
                    } elseif (isset($contentItem->text)) {
                        $errorContent .= $contentItem->text;
                    }
                }

                return [
                    'success' => false,
                    'content' => '',
                    'error' => $errorContent ?: 'Tool execution failed'
                ];
            }
        } catch (McpClientException $e) {
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
        return array_keys($this->clients);
    }

    /**
     * Check if a server is connected
     *
     * @param string $serverName
     * @return bool
     */
    public function isConnected(string $serverName): bool
    {
        return isset($this->clients[$serverName]);
    }

    /**
     * Get server information
     *
     * @param string $serverName
     * @return array{name: string, version: string, protocol_version: string}|null
     */
    public function getServerInfo(string $serverName): ?array
    {
        if (!isset($this->clients[$serverName])) {
            return null;
        }

        $client = $this->clients[$serverName];
        
        return [
            'name' => $client->getServerName(),
            'version' => $client->getServerVersion(),
            'protocol_version' => $client->getNegotiatedProtocolVersion(),
        ];
    }
}