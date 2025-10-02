<?php

namespace Nicobleiler\Mistral\SDK\Beta;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nicobleiler\Mistral\Types\Conversations\Conversation;
use Nicobleiler\Mistral\Types\Conversations\ConversationRequest;

class Conversations
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create a conversation
     *
     * @param ConversationRequest|array $params
     * @return Conversation
     * @throws GuzzleException
     */
    public function create(ConversationRequest|array $params): Conversation
    {
        if (is_array($params)) {
            $params = ConversationRequest::fromArray($params);
        }

        $response = $this->httpClient->request('POST', '/v1/conversations', [
            'json' => $params->toArray()
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return Conversation::fromArray($data);
    }

    /**
     * List conversations
     *
     * @param array{
     *     limit?: int,
     *     order?: string,
     *     after?: string,
     *     before?: string
     * } $params
     * @return array{
     *     object: string,
     *     data: Conversation[],
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

        $response = $this->httpClient->request('GET', '/v1/conversations', [
            'query' => $queryParams
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'object' => $data['object'] ?? 'list',
            'data' => array_map(fn($item) => Conversation::fromArray($item), $data['data'] ?? []),
            'first_id' => $data['first_id'] ?? null,
            'last_id' => $data['last_id'] ?? null,
            'has_more' => $data['has_more'] ?? false,
        ];
    }

    /**
     * Retrieve a conversation
     *
     * @param string $conversationId
     * @return Conversation
     * @throws GuzzleException
     */
    public function retrieve(string $conversationId): Conversation
    {
        $response = $this->httpClient->request('GET', "/v1/conversations/{$conversationId}");

        $data = json_decode($response->getBody()->getContents(), true);
        return Conversation::fromArray($data);
    }

    /**
     * Update a conversation
     *
     * @param string $conversationId
     * @param array{
     *     metadata?: array<string, mixed>
     * } $params
     * @return Conversation
     * @throws GuzzleException
     */
    public function update(string $conversationId, array $params): Conversation
    {
        $response = $this->httpClient->request('PATCH', "/v1/conversations/{$conversationId}", [
            'json' => $params
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return Conversation::fromArray($data);
    }

    /**
     * Delete a conversation
     *
     * @param string $conversationId
     * @return array{
     *     id: string,
     *     object: string,
     *     deleted: bool
     * }
     * @throws GuzzleException
     */
    public function delete(string $conversationId): array
    {
        $response = $this->httpClient->request('DELETE', "/v1/conversations/{$conversationId}");

        return json_decode($response->getBody()->getContents(), true);
    }
}
