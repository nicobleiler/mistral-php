<?php

namespace Mistral\Mcp;

use Mistral\Resources\Chat as BaseChat;
use Mistral\Types\Chat\ChatRequest;
use Mistral\Types\Chat\ChatResponse;
use Mistral\Types\Chat\Message;
use Mistral\Types\Chat\Tool;
use Mistral\Types\Chat\ToolCall;
use Mistral\Types\Chat\Function_;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Enhanced Chat Resource with MCP Tool Integration
 * 
 * Extends the base Chat resource to support MCP tool calls
 * within Mistral conversations.
 */
class McpEnabledChat extends BaseChat
{
    private McpClientManager $mcpManager;
    private LoggerInterface $logger;

    public function __construct(Client $client, McpClientManager $mcpManager, ?LoggerInterface $logger = null)
    {
        parent::__construct($client);
        $this->mcpManager = $mcpManager;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Create a chat completion with MCP tool support
     *
     * @param ChatRequest|array $params
     * @return ChatResponse
     * @throws GuzzleException
     */
    public function create(ChatRequest|array $params): ChatResponse
    {
        if (is_array($params)) {
            $params = ChatRequest::fromArray($params);
        }

        // Add available MCP tools to the request
        $params = $this->addMcpToolsToRequest($params);

        // Call the base chat completion
        $response = parent::create($params);

        // Handle any tool calls in the response
        $response = $this->handleToolCalls($response, $params);

        return $response;
    }

    /**
     * Add available MCP tools to the chat request
     */
    private function addMcpToolsToRequest(ChatRequest $params): ChatRequest
    {
        $mcpTools = $this->mcpManager->listAllTools();
        $tools = $params->tools ?? [];

        // Convert MCP tools to Mistral tool format
        foreach ($mcpTools as $serverName => $serverTools) {
            foreach ($serverTools as $tool) {
                $mistralTool = new Tool(
                    type: 'function',
                    function: new Function_(
                        name: "mcp_{$serverName}_{$tool['name']}",
                        description: $tool['description'] ?: "Tool {$tool['name']} from MCP server {$serverName}",
                        parameters: $tool['inputSchema'] ?: ['type' => 'object', 'properties' => []]
                    )
                );
                
                $tools[] = $mistralTool;
            }
        }

        if (!empty($tools)) {
            $params->tools = $tools;
            $params->tool_choice = $params->tool_choice ?? 'auto';
        }

        return $params;
    }

    /**
     * Handle tool calls in the response
     */
    private function handleToolCalls(ChatResponse $response, ChatRequest $originalRequest): ChatResponse
    {
        $choice = $response->choices[0] ?? null;
        if (!$choice || !$choice->message->tool_calls) {
            return $response;
        }

        $toolCallMessages = [];
        $hasToolCalls = false;

        foreach ($choice->message->tool_calls as $toolCall) {
            if ($this->isMcpTool($toolCall->function->name)) {
                $hasToolCalls = true;
                $result = $this->executeMcpTool($toolCall);
                
                $toolCallMessages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => $result['content'] ?? $result['error'] ?? 'Tool execution failed'
                ];
            }
        }

        // If we executed MCP tools, continue the conversation with results
        if ($hasToolCalls) {
            $messages = $originalRequest->messages;
            $messages[] = $choice->message->toArray();
            $messages = array_merge($messages, $toolCallMessages);

            $followUpRequest = new ChatRequest(
                model: $originalRequest->model,
                messages: $messages,
                temperature: $originalRequest->temperature,
                max_tokens: $originalRequest->max_tokens,
                tools: $originalRequest->tools,
                tool_choice: 'none' // Don't allow recursive tool calls
            );

            return parent::create($followUpRequest);
        }

        return $response;
    }

    /**
     * Check if a tool name is an MCP tool
     */
    private function isMcpTool(string $toolName): bool
    {
        return str_starts_with($toolName, 'mcp_');
    }

    /**
     * Execute an MCP tool call
     *
     * @param ToolCall $toolCall
     * @return array{content?: string, error?: string}
     */
    private function executeMcpTool(ToolCall $toolCall): array
    {
        // Parse MCP tool name: mcp_{server}_{tool}
        $parts = explode('_', $toolCall->function->name, 3);
        if (count($parts) < 3) {
            return ['error' => 'Invalid MCP tool name format'];
        }

        $serverName = $parts[1];
        $toolName = $parts[2];
        $arguments = $toolCall->function->arguments ?? [];

        $this->logger->info('Executing MCP tool', [
            'server' => $serverName,
            'tool' => $toolName,
            'arguments' => $arguments,
        ]);

        if (!$this->mcpManager->isConnected($serverName)) {
            try {
                $this->mcpManager->connect($serverName);
            } catch (\Exception $e) {
                return ['error' => "Failed to connect to MCP server '{$serverName}': " . $e->getMessage()];
            }
        }

        return $this->mcpManager->callTool($serverName, $toolName, $arguments);
    }

    /**
     * Get available MCP tools for display/debugging
     *
     * @return array<string, array>
     */
    public function getAvailableMcpTools(): array
    {
        return $this->mcpManager->listAllTools();
    }

    /**
     * Connect to an MCP server
     *
     * @param string $name
     * @param string $transport
     * @param array $config
     */
    public function addMcpServer(string $name, string $transport, array $config): void
    {
        $this->mcpManager->addServer($name, $transport, $config);
    }

    /**
     * Connect to a configured MCP server
     *
     * @param string $serverName
     */
    public function connectToMcpServer(string $serverName): void
    {
        $this->mcpManager->connect($serverName);
    }

    /**
     * Get MCP client manager
     */
    public function getMcpManager(): McpClientManager
    {
        return $this->mcpManager;
    }
}