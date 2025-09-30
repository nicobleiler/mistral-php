<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mistral AI API Key
    |--------------------------------------------------------------------------
    |
    | Your Mistral AI API key from https://console.mistral.ai/
    |
    */
    'api_key' => env('MISTRAL_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Mistral AI Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Mistral AI API. You typically don't need to change this.
    |
    */
    'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use for chat completions if none is specified.
    |
    */
    'default_model' => env('MISTRAL_DEFAULT_MODEL', 'mistral-tiny'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */
    'timeout' => env('MISTRAL_TIMEOUT', 30),
];
