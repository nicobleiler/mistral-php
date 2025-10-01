<?php

namespace Nicobleiler\Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Agents
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create an agent
     *
     * @param array{
     *     model: string,
     *     name: string,
     *     description?: string,
     *     instructions?: string,
     *     tools?: array<array{
     *         type: string,
     *         function?: array{
     *             name: string,
     *             description?: string,
     *             parameters?: array
     *         }
     *     }>,
     *     file_ids?: array<string>,
     *     metadata?: array<string, mixed>
     * } $params
     * @return array{
     *     id: string,
     *     object: string,
     *     created_at: int,
     *     name: string,
     *     description?: string,
     *     model: string,
     *     instructions?: string,
     *     tools: array,
     *     file_ids: array<string>,
     *     metadata: array<string, mixed>
     * }
     * @throws GuzzleException
     */
    public function create(array $params): array
    {
        $response = $this->client->request('POST', '/v1/agents', [
            'json' => $params
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * List agents
     *
     * @param array{
     *     limit?: int,
     *     order?: string,
     *     after?: string,
     *     before?: string
     * } $params
     * @return array{
     *     object: string,
     *     data: array<array{
     *         id: string,
     *         object: string,
     *         created_at: int,
     *         name: string,
     *         model: string
     *     }>,
     *     first_id?: string,
     *     last_id?: string,
     *     has_more: bool
     * }
     * @throws GuzzleException
     */
    public function list(array $params = []): array
    {
        $queryParams = [];
        if (isset($params['limit'])) {
            $queryParams['limit'] = $params['limit'];
        }
        if (isset($params['order'])) {
            $queryParams['order'] = $params['order'];
        }
        if (isset($params['after'])) {
            $queryParams['after'] = $params['after'];
        }
        if (isset($params['before'])) {
            $queryParams['before'] = $params['before'];
        }

        $response = $this->client->request('GET', '/v1/agents', [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Retrieve an agent
     *
     * @param string $agentId
     * @return array{
     *     id: string,
     *     object: string,
     *     created_at: int,
     *     name: string,
     *     description?: string,
     *     model: string,
     *     instructions?: string,
     *     tools: array,
     *     file_ids: array<string>,
     *     metadata: array<string, mixed>
     * }
     * @throws GuzzleException
     */
    public function retrieve(string $agentId): array
    {
        $response = $this->client->request('GET', "/v1/agents/{$agentId}");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Update an agent
     *
     * @param string $agentId
     * @param array{
     *     model?: string,
     *     name?: string,
     *     description?: string,
     *     instructions?: string,
     *     tools?: array<array{
     *         type: string,
     *         function?: array{
     *             name: string,
     *             description?: string,
     *             parameters?: array
     *         }
     *     }>,
     *     file_ids?: array<string>,
     *     metadata?: array<string, mixed>
     * } $params
     * @return array{
     *     id: string,
     *     object: string,
     *     created_at: int,
     *     name: string,
     *     description?: string,
     *     model: string,
     *     instructions?: string,
     *     tools: array,
     *     file_ids: array<string>,
     *     metadata: array<string, mixed>
     * }
     * @throws GuzzleException
     */
    public function update(string $agentId, array $params): array
    {
        $response = $this->client->request('POST', "/v1/agents/{$agentId}", [
            'json' => $params
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Delete an agent
     *
     * @param string $agentId
     * @return array{
     *     id: string,
     *     object: string,
     *     deleted: bool
     * }
     * @throws GuzzleException
     */
    public function delete(string $agentId): array
    {
        $response = $this->client->request('DELETE', "/v1/agents/{$agentId}");

        return json_decode($response->getBody()->getContents(), true);
    }
}
