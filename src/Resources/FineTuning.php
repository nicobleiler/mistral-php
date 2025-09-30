<?php

namespace Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FineTuning
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a fine-tuning job
     *
     * @param array{
     *     model: string,
     *     training_file: string,
     *     hyperparameters?: array{
     *         n_epochs?: int,
     *         batch_size?: int,
     *         learning_rate?: float
     *     },
     *     suffix?: string,
     *     validation_file?: string
     * } $params
     * @return array{
     *     id: string,
     *     object: string,
     *     model: string,
     *     created_at: int,
     *     events?: array,
     *     fine_tuned_model?: string,
     *     status: string,
     *     training_file: string,
     *     validation_file?: string,
     *     hyperparameters: array
     * }
     * @throws GuzzleException
     */
    public function create(array $params): array
    {
        $response = $this->client->request('POST', '/v1/fine_tuning/jobs', [
            'json' => $params
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * List fine-tuning jobs
     *
     * @param array{
     *     after?: string,
     *     limit?: int
     * } $params
     * @return array{
     *     object: string,
     *     data: array<array{
     *         id: string,
     *         object: string,
     *         model: string,
     *         created_at: int,
     *         fine_tuned_model?: string,
     *         status: string
     *     }>,
     *     has_more: bool
     * }
     * @throws GuzzleException
     */
    public function list(array $params = []): array
    {
        $queryParams = [];
        if (isset($params['after'])) {
            $queryParams['after'] = $params['after'];
        }
        if (isset($params['limit'])) {
            $queryParams['limit'] = $params['limit'];
        }

        $response = $this->client->request('GET', '/v1/fine_tuning/jobs', [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Retrieve a fine-tuning job
     *
     * @param string $jobId
     * @return array{
     *     id: string,
     *     object: string,
     *     model: string,
     *     created_at: int,
     *     events?: array,
     *     fine_tuned_model?: string,
     *     status: string,
     *     training_file: string,
     *     validation_file?: string,
     *     hyperparameters: array
     * }
     * @throws GuzzleException
     */
    public function retrieve(string $jobId): array
    {
        $response = $this->client->request('GET', "/v1/fine_tuning/jobs/{$jobId}");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Cancel a fine-tuning job
     *
     * @param string $jobId
     * @return array{
     *     id: string,
     *     object: string,
     *     model: string,
     *     created_at: int,
     *     status: string
     * }
     * @throws GuzzleException
     */
    public function cancel(string $jobId): array
    {
        $response = $this->client->request('POST', "/v1/fine_tuning/jobs/{$jobId}/cancel");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * List fine-tuning events
     *
     * @param string $jobId
     * @param array{
     *     after?: string,
     *     limit?: int
     * } $params
     * @return array{
     *     object: string,
     *     data: array<array{
     *         id: string,
     *         object: string,
     *         created_at: int,
     *         level: string,
     *         message: string
     *     }>
     * }
     * @throws GuzzleException
     */
    public function listEvents(string $jobId, array $params = []): array
    {
        $queryParams = [];
        if (isset($params['after'])) {
            $queryParams['after'] = $params['after'];
        }
        if (isset($params['limit'])) {
            $queryParams['limit'] = $params['limit'];
        }

        $response = $this->client->request('GET', "/v1/fine_tuning/jobs/{$jobId}/events", [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}