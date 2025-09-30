<?php

namespace Mistral;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Mistral\Resources\Chat;
use Mistral\Resources\Embeddings;
use Mistral\Resources\Models;

class Client
{
    private GuzzleClient $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl ?? 'https://api.mistral.ai/v1';
        
        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    public function chat(): Chat
    {
        return new Chat($this->client);
    }

    public function embeddings(): Embeddings
    {
        return new Embeddings($this->client);
    }

    public function models(): Models
    {
        return new Models($this->client);
    }

    /**
     * Make a request to the API
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $options = [];
        
        if (!empty($data)) {
            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }
        }

        $response = $this->client->request($method, $endpoint, $options);
        
        return json_decode($response->getBody()->getContents(), true);
    }
}
