<?php

namespace Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mistral\Types\Chat\ChatRequest;
use Mistral\Types\Chat\ChatResponse;

class Chat
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
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

        $response = $this->client->request('POST', '/v1/chat/completions', [
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

        $response = $this->client->request('POST', '/v1/chat/completions', [
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
