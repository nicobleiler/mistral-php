<?php

namespace Mistral\Resources;

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
     * @param array $params
     * @return array
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
