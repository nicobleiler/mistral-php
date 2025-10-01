<?php

namespace Nicobleiler\Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Embeddings
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create embeddings for the given input texts
     *
     * @param array{
     *     model: string,
     *     input: string|array<string>,
     *     encoding_format?: string,
     *     user?: string
     * } $params
     * @return array{
     *     object: string,
     *     data: array<array{
     *         object: string,
     *         embedding: array<float>,
     *         index: int
     *     }>,
     *     model: string,
     *     usage: array{
     *         prompt_tokens: int,
     *         total_tokens: int
     *     }
     * }
     * @throws GuzzleException
     */
    public function create(array $params): array
    {
        $response = $this->client->request('POST', '/v1/embeddings', [
            'json' => $params
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
