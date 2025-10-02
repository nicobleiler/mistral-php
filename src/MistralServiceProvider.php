<?php

namespace Nicobleiler\Mistral;

use Illuminate\Support\ServiceProvider;
use Nicobleiler\Mistral\SDK\Client;

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
