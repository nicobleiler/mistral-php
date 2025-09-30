# Mistral PHP

A comprehensive PHP client library for the Mistral AI API with Laravel support.

## Features

- 🚀 Full Mistral AI API support (Chat Completions, Embeddings, Models)
- 🎯 Laravel integration with service provider and facade
- 🔄 Streaming support for chat completions
- 📝 Type-safe responses
- ⚡ PSR-4 autoloading
- 🧪 Comprehensive test suite

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

This package includes comprehensive PHP type annotations for all API methods. IDE autocompletion and static analysis tools will provide full type checking for:

- **Input parameters**: All method parameters are typed with detailed array shapes
- **Return values**: Complete response structure typing for all API responses
- **Optional parameters**: Proper optional parameter handling with default values
- **Union types**: Support for parameters that accept multiple types (e.g., `string|array<string>`)

Example with full type support:

```php
// Parameters are fully typed - your IDE will show available options
$response = $client->chat()->create([
    'model' => 'mistral-tiny',              // string (required)
    'messages' => [                         // array<array{role: string, content: string, name?: string}>
        [
            'role' => 'user',               // 'user'|'assistant'|'system'
            'content' => 'Hello world',     // string
            'name' => 'user123'             // string (optional)
        ]
    ],
    'temperature' => 0.7,                   // float (optional)
    'max_tokens' => 100,                    // int (optional)
    'tools' => [                            // array<array{type: string, function: array}> (optional)
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_weather',
                'description' => 'Get current weather',
                'parameters' => [/* ... */]
            ]
        ]
    ]
]);

// Return value is also fully typed
echo $response['choices'][0]['message']['content']; // IDE knows this is a string
```

## API Reference

### Chat Completions

Create chat completions with the Mistral AI models:

```php
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