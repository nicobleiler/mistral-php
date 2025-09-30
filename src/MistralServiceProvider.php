<?php

namespace Mistral;

use Illuminate\Support\ServiceProvider;
use Mistral\Mcp\MistralMcpServer;

class MistralServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']['mistral'] ?? [];
            
            $apiKey = $config['api_key'] ?? env('MISTRAL_API_KEY');
            $baseUrl = $config['base_url'] ?? env('MISTRAL_BASE_URL');
            
            if (!$apiKey) {
                throw new \InvalidArgumentException('Mistral API key is required. Set MISTRAL_API_KEY environment variable or publish the config file.');
            }
            
            return new Client($apiKey, $baseUrl);
        });
        
        $this->app->alias(Client::class, 'mistral');

        // Register MCP server if enabled
        $this->app->singleton(MistralMcpServer::class, function ($app) {
            $mistralClient = $app->make(Client::class);
            $logger = $app->has('log') ? $app->make('log') : null;
            
            return new MistralMcpServer($mistralClient, $logger, $app);
        });
        
        $this->app->alias(MistralMcpServer::class, 'mistral.mcp');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/mistral.php' => config_path('mistral.php'),
            ], 'mistral-config');
        }
    }
}