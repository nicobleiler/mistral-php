#!/usr/bin/env php
<?php

/*
 * Mistral AI MCP Server
 * 
 * A standalone script to run a Model Context Protocol server
 * that exposes Mistral AI capabilities.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mistral\Mcp\MistralMcpServer;
use Psr\Log\LogLevel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function showUsage(): void
{
    echo "Mistral AI MCP Server\n";
    echo "Usage: php mistral-mcp-server.php [options]\n\n";
    echo "Options:\n";
    echo "  --transport=stdio|http   Transport method (default: stdio)\n";
    echo "  --host=HOST             HTTP host (default: 127.0.0.1)\n";
    echo "  --port=PORT             HTTP port (default: 8080)\n";
    echo "  --api-key=KEY           Mistral API key (or set MISTRAL_API_KEY env var)\n";
    echo "  --base-url=URL          Custom Mistral API base URL\n";
    echo "  --log-level=LEVEL       Log level (debug, info, warning, error)\n";
    echo "  --help                  Show this help message\n\n";
    echo "Examples:\n";
    echo "  php mistral-mcp-server.php --api-key=your-key\n";
    echo "  php mistral-mcp-server.php --transport=http --port=8080\n";
    echo "  MISTRAL_API_KEY=your-key php mistral-mcp-server.php\n\n";
}

function parseArgs(array $argv): array
{
    $options = [
        'transport' => 'stdio',
        'host' => '127.0.0.1',
        'port' => 8080,
        'api-key' => null,
        'base-url' => null,
        'log-level' => 'info',
        'help' => false,
    ];

    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];
        
        if ($arg === '--help' || $arg === '-h') {
            $options['help'] = true;
            continue;
        }

        if (str_starts_with($arg, '--')) {
            $parts = explode('=', $arg, 2);
            $key = substr($parts[0], 2);
            $value = $parts[1] ?? true;

            if (array_key_exists($key, $options)) {
                $options[$key] = $value;
            } else {
                echo "Unknown option: $key\n";
                exit(1);
            }
        }
    }

    return $options;
}

function createLogger(string $level): Logger
{
    $logger = new Logger('mistral-mcp');
    
    $logLevel = match (strtolower($level)) {
        'debug' => LogLevel::DEBUG,
        'info' => LogLevel::INFO,
        'warning' => LogLevel::WARNING,
        'error' => LogLevel::ERROR,
        default => LogLevel::INFO,
    };

    $handler = new StreamHandler('php://stderr', $logLevel);
    $logger->pushHandler($handler);

    return $logger;
}

// Parse command line arguments
$options = parseArgs($argv);

if ($options['help']) {
    showUsage();
    exit(0);
}

// Get API key from options or environment
$apiKey = $options['api-key'] ?? $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');

if (!$apiKey) {
    echo "Error: Mistral API key is required.\n";
    echo "Set it via --api-key option or MISTRAL_API_KEY environment variable.\n\n";
    showUsage();
    exit(1);
}

// Create logger
$logger = createLogger($options['log-level']);

try {
    // Create MCP server
    $server = MistralMcpServer::create(
        $apiKey,
        $options['base-url'],
        $logger
    );

    $logger->info('Mistral AI MCP Server starting...', [
        'transport' => $options['transport'],
        'host' => $options['host'],
        'port' => $options['port'],
    ]);

    // Start server with appropriate transport
    if ($options['transport'] === 'http') {
        $server->runHttp($options['host'], (int) $options['port']);
    } else {
        $server->runStdio();
    }

} catch (Exception $e) {
    $logger->error('Failed to start MCP server', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    exit(1);
}