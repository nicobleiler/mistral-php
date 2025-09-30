<?php

namespace Mistral\Mcp;

use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpTool;
use Mistral\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * MCP Tools and Resources for Mistral AI
 * 
 * Provides Model Context Protocol integration for Mistral AI capabilities
 * including chat completions, embeddings, models, and file operations.
 */
final class MistralMcpElements
{
    public function __construct(
        private readonly Client $mistralClient,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * Create a chat completion using Mistral AI
     *
     * @param string $model The model to use (e.g., 'mistral-tiny', 'mistral-small', 'mistral-medium')
     * @param string $message The user message to send
     * @param float|null $temperature The sampling temperature (0.0 to 1.0)
     * @param int|null $maxTokens Maximum number of tokens to generate
     * @param string|null $systemPrompt Optional system prompt to set context
     *
     * @return array{
     *     content: string,
     *     model: string,
     *     usage?: array{
     *         prompt_tokens: int,
     *         completion_tokens: int,
     *         total_tokens: int
     *     },
     *     error?: string
     * }
     */
    #[McpTool(name: 'mistral_chat')]
    public function chat(
        string $model,
        string $message,
        ?float $temperature = null,
        ?int $maxTokens = null,
        ?string $systemPrompt = null
    ): array {
        $this->logger->info('Mistral chat completion requested', [
            'model' => $model,
            'message_length' => strlen($message),
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        try {
            $messages = [];
            
            if ($systemPrompt) {
                $messages[] = ['role' => 'system', 'content' => $systemPrompt];
            }
            
            $messages[] = ['role' => 'user', 'content' => $message];

            $params = [
                'model' => $model,
                'messages' => $messages,
            ];

            if ($temperature !== null) {
                $params['temperature'] = $temperature;
            }

            if ($maxTokens !== null) {
                $params['max_tokens'] = $maxTokens;
            }

            $response = $this->mistralClient->chat()->create($params);

            return [
                'content' => $response->choices[0]->message->content,
                'model' => $response->model,
                'usage' => [
                    'prompt_tokens' => $response->usage->prompt_tokens,
                    'completion_tokens' => $response->usage->completion_tokens,
                    'total_tokens' => $response->usage->total_tokens,
                ],
            ];
        } catch (\Exception $e) {
            $this->logger->error('Mistral chat completion failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            return [
                'content' => '',
                'model' => $model,
                'error' => 'Failed to generate chat completion: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate embeddings for given text using Mistral AI
     *
     * @param string $text The text to embed
     * @param string $model The embedding model to use (default: 'mistral-embed')
     *
     * @return array{
     *     embeddings: array<float>,
     *     model: string,
     *     usage?: array{
     *         prompt_tokens: int,
     *         total_tokens: int
     *     },
     *     error?: string
     * }
     */
    #[McpTool(name: 'mistral_embed')]
    public function embed(string $text, string $model = 'mistral-embed'): array
    {
        $this->logger->info('Mistral embedding requested', [
            'model' => $model,
            'text_length' => strlen($text),
        ]);

        try {
            $response = $this->mistralClient->embeddings()->create([
                'model' => $model,
                'input' => [$text],
            ]);

            return [
                'embeddings' => $response['data'][0]['embedding'],
                'model' => $response['model'],
                'usage' => $response['usage'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Mistral embedding failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            return [
                'embeddings' => [],
                'model' => $model,
                'error' => 'Failed to generate embeddings: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * List available Mistral AI models
     *
     * @return array{
     *     models: array<array{
     *         id: string,
     *         object: string,
     *         created: int,
     *         owned_by: string
     *     }>,
     *     error?: string
     * }
     */
    #[McpTool(name: 'mistral_list_models')]
    public function listModels(): array
    {
        $this->logger->info('Mistral models list requested');

        try {
            $response = $this->mistralClient->models()->list();

            return [
                'models' => $response['data'],
            ];
        } catch (\Exception $e) {
            $this->logger->error('Mistral models list failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'models' => [],
                'error' => 'Failed to list models: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get details about a specific Mistral AI model
     *
     * @param string $modelId The model ID to get details for
     *
     * @return array{
     *     model: array{
     *         id: string,
     *         object: string,
     *         created: int,
     *         owned_by: string
     *     }|null,
     *     error?: string
     * }
     */
    #[McpTool(name: 'mistral_get_model')]
    public function getModel(string $modelId): array
    {
        $this->logger->info('Mistral model details requested', ['model_id' => $modelId]);

        try {
            $response = $this->mistralClient->models()->get($modelId);

            return [
                'model' => $response,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Mistral model details failed', [
                'error' => $e->getMessage(),
                'model_id' => $modelId,
            ]);

            return [
                'model' => null,
                'error' => 'Failed to get model details: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Provides information about available Mistral AI models and their capabilities
     * 
     * @return array{
     *     models: array<array{
     *         id: string,
     *         description: string,
     *         capabilities: array<string>
     *     }>
     * }
     */
    #[McpResource(
        uri: 'mistral://models/info',
        name: 'mistral_models_info',
        description: 'Information about available Mistral AI models and their capabilities',
        mimeType: 'application/json',
    )]
    public function getModelsInfo(): array
    {
        $this->logger->info('Mistral models info resource accessed');

        return [
            'models' => [
                [
                    'id' => 'mistral-tiny',
                    'description' => 'Fast and efficient model for simple tasks',
                    'capabilities' => ['text-generation', 'conversation'],
                ],
                [
                    'id' => 'mistral-small',
                    'description' => 'Balanced model for general-purpose tasks',
                    'capabilities' => ['text-generation', 'conversation', 'reasoning'],
                ],
                [
                    'id' => 'mistral-medium',
                    'description' => 'Advanced model for complex reasoning tasks',
                    'capabilities' => ['text-generation', 'conversation', 'reasoning', 'analysis'],
                ],
                [
                    'id' => 'mistral-large',
                    'description' => 'Most capable model for demanding tasks',
                    'capabilities' => ['text-generation', 'conversation', 'reasoning', 'analysis', 'code-generation'],
                ],
                [
                    'id' => 'mistral-embed',
                    'description' => 'Specialized model for generating text embeddings',
                    'capabilities' => ['embeddings', 'similarity-search'],
                ],
            ],
        ];
    }

    /**
     * Provides the current Mistral client configuration
     * 
     * @return array{
     *     base_url: string,
     *     timeout: int,
     *     version: string
     * }
     */
    #[McpResource(
        uri: 'mistral://config/client',
        name: 'mistral_client_config',
        description: 'Current configuration of the Mistral client',
        mimeType: 'application/json',
    )]
    public function getClientConfig(): array
    {
        $this->logger->info('Mistral client config resource accessed');

        return [
            'base_url' => 'https://api.mistral.ai/v1',
            'timeout' => 30,
            'version' => '1.0.0',
        ];
    }
}