<?php

namespace Mistral\Mcp;

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Server\Transport\HttpTransport;
use Mcp\Capability\Registry\Container;
use Mistral\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Container\ContainerInterface;

/**
 * MCP Server for Mistral AI
 * 
 * Creates and manages an MCP server that exposes Mistral AI capabilities
 * through the Model Context Protocol.
 */
class MistralMcpServer
{
    private Server $server;
    private MistralMcpElements $elements;

    public function __construct(
        private readonly Client $mistralClient,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?ContainerInterface $container = null
    ) {
        $this->elements = new MistralMcpElements($this->mistralClient, $this->logger ?? new NullLogger());
        $this->initializeServer();
    }

    /**
     * Initialize the MCP server with Mistral AI capabilities
     */
    private function initializeServer(): void
    {
        $logger = $this->logger ?? new NullLogger();
        
        // Create a container and register our elements instance
        $container = new Container();
        $container->set(LoggerInterface::class, $logger);
        $container->set(MistralMcpElements::class, $this->elements);
        
        $builder = Server::builder()
            ->setServerInfo(
                'Mistral AI MCP Server',
                '1.0.0',
                'Model Context Protocol server for Mistral AI integration'
            )
            ->setLogger($logger)
            ->setContainer($container);

        // Use provided container if available, otherwise use our own
        if ($this->container !== null) {
            $builder->setContainer($this->container);
        }

        // Manually register tools
        $builder->addTool([MistralMcpElements::class, 'chat'], 'mistral_chat', 'Create a chat completion using Mistral AI');
        $builder->addTool([MistralMcpElements::class, 'embed'], 'mistral_embed', 'Generate embeddings for given text using Mistral AI');
        $builder->addTool([MistralMcpElements::class, 'listModels'], 'mistral_list_models', 'List available Mistral AI models');
        $builder->addTool([MistralMcpElements::class, 'getModel'], 'mistral_get_model', 'Get details about a specific Mistral AI model');

        // Manually register resources
        $builder->addResource([MistralMcpElements::class, 'getModelsInfo'], 'mistral://models/info', 'mistral_models_info', 
            'Information about available Mistral AI models and their capabilities', 'application/json');
        $builder->addResource([MistralMcpElements::class, 'getClientConfig'], 'mistral://config/client', 'mistral_client_config', 
            'Current configuration of the Mistral client', 'application/json');

        $this->server = $builder->build();
    }

    /**
     * Start the MCP server with STDIO transport
     * 
     * This is suitable for command-line integration and most MCP clients.
     */
    public function runStdio(): void
    {
        $logger = $this->logger ?? new NullLogger();
        $logger->info('Starting Mistral AI MCP server with STDIO transport');

        $transport = new StdioTransport(logger: $logger);
        $this->server->connect($transport);
        $transport->listen();

        $logger->info('Mistral AI MCP server stopped gracefully');
    }

    /**
     * Start the MCP server with HTTP transport
     * 
     * @param string $host The host to bind to (default: '127.0.0.1')
     * @param int $port The port to bind to (default: 8080)
     */
    public function runHttp(string $host = '127.0.0.1', int $port = 8080): void
    {
        $logger = $this->logger ?? new NullLogger();
        $logger->info('Starting Mistral AI MCP server with HTTP transport', [
            'host' => $host,
            'port' => $port,
        ]);

        $transport = new HttpTransport($host, $port, logger: $logger);
        $this->server->connect($transport);
        $transport->listen();

        $logger->info('Mistral AI MCP server stopped gracefully');
    }

    /**
     * Get the underlying MCP server instance
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * Get the Mistral MCP elements instance
     */
    public function getElements(): MistralMcpElements
    {
        return $this->elements;
    }

    /**
     * Create an MCP server with a Mistral client using API key
     * 
     * @param string $apiKey The Mistral API key
     * @param string|null $baseUrl Optional custom base URL
     * @param LoggerInterface|null $logger Optional logger
     * @param ContainerInterface|null $container Optional DI container
     */
    public static function create(
        string $apiKey,
        ?string $baseUrl = null,
        ?LoggerInterface $logger = null,
        ?ContainerInterface $container = null
    ): self {
        $client = new Client($apiKey, $baseUrl);
        $logger = $logger ?? new NullLogger();

        return new self($client, $logger, $container);
    }
}