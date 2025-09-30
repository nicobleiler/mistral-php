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