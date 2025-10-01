<?php

namespace Nicobleiler\Mistral;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Nicobleiler\Mistral\Resources\Agents;
use Nicobleiler\Mistral\Resources\Chat;
use Nicobleiler\Mistral\Resources\Conversations;
use Nicobleiler\Mistral\Resources\Embeddings;
use Nicobleiler\Mistral\Resources\Files;
use Nicobleiler\Mistral\Resources\FineTuning;
use Nicobleiler\Mistral\Resources\Models;
use Nicobleiler\Mistral\Mcp\McpClientManager;
use Nicobleiler\Mistral\Mcp\McpEnabledChat;
use Psr\Log\LoggerInterface;

class Client
{
    private GuzzleClient $client;
    private string $apiKey;
    private string $baseUrl;
    private ?McpClientManager $mcpManager = null;

    public function __construct(string $apiKey, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl ?? 'https://api.mistral.ai/v1';

        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    public function chat(): Chat
    {
        return new Chat($this->client);
    }

    /**
     * Get MCP-enabled chat resource
     * 
     * @param LoggerInterface|null $logger Optional logger for MCP operations
     * @return McpEnabledChat
     */
    public function mcpChat(?LoggerInterface $logger = null): McpEnabledChat
    {
        if ($this->mcpManager === null) {
            $this->mcpManager = new McpClientManager($logger);
        }

        return new McpEnabledChat($this->client, $this->mcpManager, $logger);
    }

    public function embeddings(): Embeddings
    {
        return new Embeddings($this->client);
    }

    public function models(): Models
    {
        return new Models($this->client);
    }

    public function files(): Files
    {
        return new Files($this->client);
    }

    public function fineTuning(): FineTuning
    {
        return new FineTuning($this->client);
    }

    public function agents(): Agents
    {
        return new Agents($this->client);
    }

    public function conversations(): Conversations
    {
        return new Conversations($this->client);
    }

    /**
     * Make a request to the API
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $options = [];

        if (!empty($data)) {
            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }
        }

        $response = $this->client->request($method, $endpoint, $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get or create MCP client manager
     * 
     * @param LoggerInterface|null $logger Optional logger for MCP operations
     * @return McpClientManager
     */
    public function getMcpManager(?LoggerInterface $logger = null): McpClientManager
    {
        if ($this->mcpManager === null) {
            $this->mcpManager = new McpClientManager($logger);
        }

        return $this->mcpManager;
    }

    /**
     * Add an MCP server configuration
     * 
     * @param string $name Server identifier
     * @param string $transport Transport type ('stdio' or 'http')
     * @param array $config Configuration options
     */
    public function addMcpServer(string $name, string $transport, array $config): void
    {
        $this->getMcpManager()->addServer($name, $transport, $config);
    }

    /**
     * Connect to an MCP server
     * 
     * @param string $serverName
     */
    public function connectToMcpServer(string $serverName): void
    {
        $this->getMcpManager()->connect($serverName);
    }
}
