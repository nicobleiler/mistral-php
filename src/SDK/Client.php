<?php

namespace Nicobleiler\Mistral\SDK;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Nicobleiler\Mistral\SDK\Agents;
use Nicobleiler\Mistral\SDK\Chat;
use Nicobleiler\Mistral\SDK\Conversations;
use Nicobleiler\Mistral\SDK\Files;

class Client
{
    private GuzzleClient $httpClient;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl ?? 'https://api.mistral.ai';

        $this->httpClient = new GuzzleClient([
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
        return new Chat($this->httpClient);
    }

    public function files(): Files
    {
        return new Files($this->httpClient);
    }

    public function agents(): Agents
    {
        return new Agents($this->httpClient);
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

        $response = $this->httpClient->request($method, $endpoint, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
