<?php

namespace Nicobleiler\Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Files
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Upload a file
     *
     * @param array{
     *     file: resource|string,
     *     purpose: string
     * } $params
     * @return array{
     *     id: string,
     *     object: string,
     *     bytes: int,
     *     created_at: int,
     *     filename: string,
     *     purpose: string
     * }
     * @throws GuzzleException
     */
    public function upload(array $params): array
    {
        $multipart = [
            [
                'name' => 'purpose',
                'contents' => $params['purpose']
            ]
        ];

        if (is_resource($params['file'])) {
            $multipart[] = [
                'name' => 'file',
                'contents' => $params['file']
            ];
        } else {
            // Handle file path - check if file exists first
            if (!file_exists($params['file'])) {
                throw new \InvalidArgumentException("File not found: {$params['file']}");
            }
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($params['file'], 'r'),
                'filename' => basename($params['file'])
            ];
        }

        $response = $this->client->request('POST', '/v1/files', [
            'multipart' => $multipart
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * List files
     *
     * @param array{
     *     purpose?: string
     * } $params
     * @return array{
     *     object: string,
     *     data: array<array{
     *         id: string,
     *         object: string,
     *         bytes: int,
     *         created_at: int,
     *         filename: string,
     *         purpose: string
     *     }>
     * }
     * @throws GuzzleException
     */
    public function list(array $params = []): array
    {
        $queryParams = [];
        if (isset($params['purpose'])) {
            $queryParams['purpose'] = $params['purpose'];
        }

        $response = $this->client->request('GET', '/v1/files', [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Retrieve a file
     *
     * @param string $fileId
     * @return array{
     *     id: string,
     *     object: string,
     *     bytes: int,
     *     created_at: int,
     *     filename: string,
     *     purpose: string
     * }
     * @throws GuzzleException
     */
    public function retrieve(string $fileId): array
    {
        $response = $this->client->request('GET', "/v1/files/{$fileId}");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Delete a file
     *
     * @param string $fileId
     * @return array{
     *     id: string,
     *     object: string,
     *     deleted: bool
     * }
     * @throws GuzzleException
     */
    public function delete(string $fileId): array
    {
        $response = $this->client->request('DELETE', "/v1/files/{$fileId}");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Download file content
     *
     * @param string $fileId
     * @return string
     * @throws GuzzleException
     */
    public function download(string $fileId): string
    {
        $response = $this->client->request('GET', "/v1/files/{$fileId}/content");

        return $response->getBody()->getContents();
    }
}
