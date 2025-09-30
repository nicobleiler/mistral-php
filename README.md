# Mistral PHP

A comprehensive PHP client library for the Mistral AI API with Laravel support.

## Features

- 🚀 Full Mistral AI API support (Chat Completions, Embeddings, Models, Conversations)
- 🎯 Laravel integration with service provider and facade
- 🔄 Streaming support for chat completions
- 📝 Type-safe responses with PHP classes
- ⚡ PSR-4 autoloading
- 🧪 Comprehensive test suite
- 🔄 Backward compatibility with array-based API

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
use Mistral\Client;

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
use Mistral\Facades\Mistral;

// Using the facade
$response = Mistral::chat()->create([
    'model' => 'mistral-tiny', 
    'messages' => [
        ['role' => 'user', 'content' => 'Hello from Laravel!']
    ]
]);

// Or inject the client
use Mistral\Client;

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
use Mistral\Types\Chat\ChatRequest;
use Mistral\Types\Chat\Message;

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
use Mistral\Types\Chat\ChatRequest;
use Mistral\Types\Chat\Message;

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
use Mistral\Types\Conversations\ConversationRequest;

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