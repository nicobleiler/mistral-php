<?php

namespace Nicobleiler\Mistral\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nicobleiler\Mistral\Types\Chat\ChatRequest;
use Nicobleiler\Mistral\Types\Chat\ChatResponse;

class Chat
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create a chat completion
     *
     * @param ChatRequest|array $params
     * @return ChatResponse
     * @throws GuzzleException
     */
    public function create(ChatRequest|array $params): ChatResponse
    {
        if (is_array($params)) {
            $params = ChatRequest::fromArray($params);
        }

        $response = $this->httpClient->request('POST', '/v1/chat/completions', [
            'json' => $params->toArray()
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return ChatResponse::fromArray($data);
    }

    /**
     * Create a streaming chat completion
     *
     * @param ChatRequest|array $params
     * @param callable(array): void $callback
     * @return void
     * @throws GuzzleException
     */
    public function stream(ChatRequest|array $params, callable $callback): void
    {
        if ($params instanceof ChatRequest) {
            $params = $params->toArray();
        }

        $params['stream'] = true;

        $response = $this->httpClient->request('POST', '/v1/chat/completions', [
            'json' => $params,
            'stream' => true
        ]);

        $body = $response->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);

            if (empty($line)) {
                continue;
            }

            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);

                if ($data === '[DONE]') {
                    break;
                }

                $chunk = json_decode($data, true);
                if ($chunk !== null) {
                    $callback($chunk);
                }
            }
        }
    }

    private function readLine($stream): string
    {
        $line = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }
        return trim($line);
    }
}
