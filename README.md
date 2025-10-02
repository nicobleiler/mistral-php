# Mistral PHP

A comprehensive PHP client library for the Mistral AI API with Laravel support.

## Features

- [WIP] Full Mistral AI API support (Chat Completions, Embeddings, Models, Conversations)
- [WIP] Model Context Protocol (MCP) client integration for external tool calling

## Installation

Install the package via Composer:

```bash
composer require nicobleiler/mistral-php
```

### Laravel

The package will automatically register its service provider in Laravel 5.5+.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=mistral-config
```

Add your Mistral AI API key to your `.env` file:

```env
MISTRAL_API_KEY=your-api-key-here
```

## Usage

### Basic Usage

```php
use Nicobleiler\Mistral\SDK;

$client = new Client('your-api-key');

// Chat completion
$response = $client->chat()->create([
    'model' => 'mistral-tiny',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, how are you?']
    ]
]);

echo $response['choices'][0]['message']['content'];
```

### Laravel Usage

```php
use Nicobleiler\Mistral\Facades\Mistral;

// Using the facade
$response = Mistral::chat()->create([
    'model' => 'mistral-tiny', 
    'messages' => [
        ['role' => 'user', 'content' => 'Hello from Laravel!']
    ]
]);

// Or inject the client
use Nicobleiler\Mistral\SDK;

class ChatController extends Controller 
{
    public function __construct(private Client $mistral) {}
    
    public function chat(Request $request)
    {
        $response = $this->mistral->chat()->create([
            'model' => 'mistral-tiny',
            'messages' => $request->get('messages')
        ]);
        
        return response()->json($response);
    }
    
    public function uploadTrainingFile(Request $request)
    {
        $file = $this->mistral->files()->upload([
            'file' => $request->file('training_file')->getRealPath(),
            'purpose' => 'fine-tune'
        ]);
        
        return response()->json($file);
    }
    
    public function createFineTuneJob(Request $request)
    {
        $job = $this->mistral->fineTuning()->create([
            'model' => $request->get('base_model', 'mistral-tiny'),
            'training_file' => $request->get('training_file_id'),
            'hyperparameters' => $request->get('hyperparameters', [])
        ]);
        
        return response()->json($job);
    }
}
```

### Streaming Chat

```php
$client->chat()->stream([
    'model' => 'mistral-tiny',
    'messages' => [
        ['role' => 'user', 'content' => 'Tell me a story']
    ]
], function ($chunk) {
    if (isset($chunk['choices'][0]['delta']['content'])) {
        echo $chunk['choices'][0]['delta']['content'];
    }
});
```

### Embeddings

```php
$response = $client->embeddings()->create([
    'model' => 'mistral-embed',
    'input' => ['Hello world', 'How are you?']
]);

$embeddings = $response['data'];
```

### Models

```php
// List all models
$models = $client->models()->list();

// Get specific model
$model = $client->models()->get('mistral-tiny');
```

### Files

Upload and manage files for use with fine-tuning:

```php
// Upload a file
$file = $client->files()->upload([
    'file' => '/path/to/training-data.jsonl',
    'purpose' => 'fine-tune'
]);

// List files
$files = $client->files()->list();

// Retrieve a file
$fileInfo = $client->files()->retrieve($file['id']);

// Download file content
$content = $client->files()->download($file['id']);

// Delete a file
$deleted = $client->files()->delete($file['id']);
```

### Fine-tuning

Create and manage fine-tuning jobs:

```php
// Create a fine-tuning job
$job = $client->fineTuning()->create([
    'model' => 'mistral-tiny',
    'training_file' => $file['id'],
    'hyperparameters' => [
        'n_epochs' => 4,
        'batch_size' => 32,
        'learning_rate' => 0.0001
    ]
]);

// List fine-tuning jobs
$jobs = $client->fineTuning()->list();

// Retrieve a job
$jobInfo = $client->fineTuning()->retrieve($job['id']);

// Cancel a job
$cancelled = $client->fineTuning()->cancel($job['id']);

// List job events
$events = $client->fineTuning()->listEvents($job['id']);
```

### Agents

Create and manage conversational AI agents:

```php
// Create an agent
$agent = $client->agents()->create([
    'model' => 'mistral-large',
    'name' => 'Math Tutor',
    'description' => 'A helpful math tutoring agent',
    'instructions' => 'You are a personal math tutor. Help students with math problems step by step.',
    'tools' => [
        [
            'type' => 'function',
            'function' => [
                'name' => 'calculate',
                'description' => 'Perform mathematical calculations',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'expression' => ['type' => 'string']
                    ]
                ]
            ]
        ]
    ]
]);

// List agents
$agents = $client->agents()->list();

// Retrieve an agent
$agentInfo = $client->agents()->retrieve($agent['id']);

// Update an agent
$updated = $client->agents()->update($agent['id'], [
    'name' => 'Advanced Math Tutor',
    'instructions' => 'You are an advanced math tutor specializing in calculus and linear algebra.'
]);

// Delete an agent
$deleted = $client->agents()->delete($agent['id']);
```

## Type Safety

This package includes comprehensive PHP type safety through actual PHP classes, not just PHPDoc annotations. IDE autocompletion and static analysis tools will provide full type checking for:

- **Input parameters**: Typed request classes with fluent builder patterns
- **Return values**: Typed response classes with proper object hierarchies  
- **Backward compatibility**: Existing array-based code continues to work
- **IDE support**: Full autocompletion and IntelliSense in modern IDEs

Example with full type support:

```php
use Nicobleiler\Mistral\Types\Chat\ChatRequest;
use Nicobleiler\Mistral\Types\Chat\Message;

// Create typed request objects
$messages = [
    new Message('user', 'Hello world', 'user123'), // Full IDE completion
    new Message('assistant', 'Hello! How can I help?')
];

$request = new ChatRequest(
    model: 'mistral-tiny',              // string (required)
    messages: $messages,                // Message[] - strongly typed
    temperature: 0.7,                   // float (optional)
    max_tokens: 100                     // int (optional)
);

// Make request and get typed response
$response = $client->chat()->create($request); // Returns ChatResponse object

// Access response with full type safety
echo $response->choices[0]->message->content; // IDE knows exact types
echo $response->usage->total_tokens;          // No guessing about structure
```

## API Reference

### Chat Completions

Create chat completions with the Mistral AI models using either arrays (for backward compatibility) or typed objects (for enhanced type safety):

```php
// Using arrays (backward compatible)
$response = $client->chat()->create([
    'model' => 'mistral-tiny',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => 'What is the capital of France?']
    ],
    'temperature' => 0.7,
    'max_tokens' => 100,
    'top_p' => 1,
    'stream' => false
]);

// Using typed objects (recommended for new code)
use Nicobleiler\Mistral\Types\Chat\ChatRequest;
use Nicobleiler\Mistral\Types\Chat\Message;

$messages = [
    new Message('system', 'You are a helpful assistant.'),
    new Message('user', 'What is the capital of France?')
];

$request = new ChatRequest(
    model: 'mistral-tiny',
    messages: $messages,
    temperature: 0.7,
    max_tokens: 100
);

$response = $client->chat()->create($request); // Returns ChatResponse object
echo $response->choices[0]->message->content; // Full IDE autocompletion
```

### Conversations

Manage conversations with AI agents:

```php
use Nicobleiler\Mistral\Types\Conversations\ConversationRequest;

// Create a conversation
$request = new ConversationRequest(
    agent_id: 'agent-123',
    metadata: ['topic' => 'programming-help']
);
$conversation = $client->conversations()->create($request);

// List conversations
$conversations = $client->conversations()->list([
    'limit' => 10,
    'order' => 'desc'
]);

// Retrieve a conversation
$conversation = $client->conversations()->retrieve('conv-abc123');

// Update a conversation
$updated = $client->conversations()->update('conv-abc123', [
    'metadata' => ['status' => 'active']
]);

// Delete a conversation
$deleted = $client->conversations()->delete('conv-abc123');
```

### Streaming

For real-time responses:

```php
$client->chat()->stream([
    'model' => 'mistral-tiny',
    'messages' => [['role' => 'user', 'content' => 'Count to 10']]
], function ($chunk) {
    // Handle each chunk of the response
    if (isset($chunk['choices'][0]['delta']['content'])) {
        echo $chunk['choices'][0]['delta']['content'];
        flush();
    }
});
```

## Model Context Protocol (MCP) Client Integration

This package includes MCP client support using the `logiscape/mcp-sdk-php` package, allowing Mistral AI to call external MCP tools during conversations. This enables powerful integrations with external services and tools.

### Quick Start with MCP

Connect to external MCP servers and use their tools in Mistral conversations:

```php
use Nicobleiler\Mistral\SDK;

$client = new Client('your-api-key');

// Add an MCP server (stdio transport)
$client->addMcpServer('file-tools', 'stdio', [
    'command' => 'python',
    'args' => ['file_server.py'],
    'working_dir' => '/path/to/server'
]);

// Add an MCP server (HTTP transport)
$client->addMcpServer('web-tools', 'http', [
    'url' => 'http://localhost:8080'
]);

// Use MCP-enabled chat
$mcpChat = $client->mcpChat();

// Connect to servers
$mcpChat->connectToMcpServer('file-tools');
$mcpChat->connectToMcpServer('web-tools');

// Chat with access to MCP tools
$response = $mcpChat->create([
    'model' => 'mistral-large',
    'messages' => [
        ['role' => 'user', 'content' => 'Can you read the file config.json and summarize it?']
    ]
]);

echo $response->choices[0]->message->content;
```

### Available MCP Tools

View available tools from connected MCP servers:

```php
$mcpChat = $client->mcpChat();
$mcpChat->connectToMcpServer('file-tools');

$tools = $mcpChat->getAvailableMcpTools();
foreach ($tools as $serverName => $serverTools) {
    echo "Server: {$serverName}\n";
    foreach ($serverTools as $tool) {
        echo "  - {$tool['name']}: {$tool['description']}\n";
    }
}
```

### Manual Tool Execution

You can also call MCP tools directly:

```php
$mcpManager = $client->getMcpManager();
$mcpManager->addServer('calculator', 'stdio', [
    'command' => 'python',
    'args' => ['calculator_server.py']
]);
$mcpManager->connect('calculator');

$result = $mcpManager->callTool('calculator', 'add', [
    'a' => 5,
    'b' => 3
]);

if ($result['success']) {
    echo "Result: " . $result['content'];
} else {
    echo "Error: " . $result['error'];
}
```

### MCP Server Configuration

#### STDIO Transport (subprocess)
```php
$client->addMcpServer('my-server', 'stdio', [
    'command' => 'python',           // Executable command
    'args' => ['server.py'],         // Command arguments
    'working_dir' => '/path/to/dir', // Working directory
    'timeout' => 30,                 // Request timeout in seconds
    'env' => [                       // Environment variables
        'API_KEY' => 'secret'
    ]
]);
```

#### HTTP Transport
```php
$client->addMcpServer('my-server', 'http', [
    'url' => 'http://localhost:8080', // Server URL
    'timeout' => 30,                  // Request timeout
    'headers' => [                    // Additional HTTP headers
        'Authorization' => 'Bearer token'
    ]
]);
```

### Laravel Integration

In Laravel, you can configure MCP servers in your service provider:

```php
use Nicobleiler\Mistral\Facades\Mistral;

// In a service provider or controller
$mcpChat = Mistral::mcpChat();
$mcpChat->addMcpServer('tools', 'stdio', [
    'command' => 'python',
    'args' => [storage_path('mcp/tools_server.py')]
]);

$response = $mcpChat->create([
    'model' => 'mistral-large',
    'messages' => [
        ['role' => 'user', 'content' => 'Use the weather tool to get current weather for Paris']
    ]
]);
```

### Automatic Tool Integration

When using `mcpChat()`, available MCP tools are automatically added to the conversation context. Mistral can then choose to call these tools as needed during the conversation.

The tool calls happen automatically:
1. Mistral decides to use an MCP tool based on the conversation
2. The tool is called on the appropriate MCP server
3. The results are fed back to Mistral
4. Mistral incorporates the results into its response

### Error Handling

MCP operations include comprehensive error handling:

```php
try {
    $mcpChat = $client->mcpChat();
    $mcpChat->connectToMcpServer('my-server');
    
    $response = $mcpChat->create([
        'model' => 'mistral-large',
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ]);
} catch (\PhpMcp\Client\Exception\McpClientException $e) {
    echo "MCP Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "General Error: " . $e->getMessage();
}
```

## Configuration

### Environment Variables

- `MISTRAL_API_KEY` - Your Mistral AI API key (required)
- `MISTRAL_BASE_URL` - Custom base URL (optional, defaults to https://api.mistral.ai/v1)
- `MISTRAL_DEFAULT_MODEL` - Default model to use (optional, defaults to mistral-tiny)
- `MISTRAL_TIMEOUT` - Request timeout in seconds (optional, defaults to 30)

### Laravel Configuration

After publishing the config file, you can customize settings in `config/mistral.php`:

```php
return [
    'api_key' => env('MISTRAL_API_KEY'),
    'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
    'default_model' => env('MISTRAL_DEFAULT_MODEL', 'mistral-tiny'),
    'timeout' => env('MISTRAL_TIMEOUT', 30),
];
```

## Available Models

- `mistral-tiny` - Fast and efficient for simple tasks
- `mistral-small` - Good balance of speed and capability  
- `mistral-medium` - Higher capability for complex tasks
- `mistral-large` - Most capable model
- `mistral-embed` - For generating embeddings

## Error Handling

The client throws `GuzzleHttp\Exception\GuzzleException` for HTTP errors:

```php
use GuzzleHttp\Exception\GuzzleException;

try {
    $response = $client->chat()->create([
        'model' => 'mistral-tiny',
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ]);
} catch (GuzzleException $e) {
    echo "API Error: " . $e->getMessage();
}
```

## Testing

Run the test suite:

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).