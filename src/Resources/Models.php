<?php

namespace Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Models
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * List available models
     *
     * @return array{
     *     object: string,
     *     data: array<array{
     *         id: string,
     *         object: string,
     *         created: int,
     *         owned_by: string,
     *         permissions?: array,
     *         root?: string,
     *         parent?: string
     *     }>
     * }
     * @throws GuzzleException
     */
    public function list(): array
    {
        $response = $this->client->request('GET', '/v1/models');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a specific model
     *
     * @param string $modelId
     * @return array{
     *     id: string,
     *     object: string,
     *     created: int,
     *     owned_by: string,
     *     permissions?: array,
     *     root?: string,
     *     parent?: string
     * }
     * @throws GuzzleException
     */
    public function get(string $modelId): array
    {
        $response = $this->client->request('GET', "/v1/models/{$modelId}");

        return json_decode($response->getBody()->getContents(), true);
    }
}
