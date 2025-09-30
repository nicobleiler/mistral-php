<?php

namespace Mistral\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
     * @param array $params
     * @return array
     * @throws GuzzleException
     */
    public function create(array $params): array
    {
        $response = $this->client->request('POST', '/chat/completions', [
            'json' => $params
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Create a streaming chat completion
     *
     * @param array $params
     * @param callable $callback
     * @return void
     * @throws GuzzleException
     */
    public function stream(array $params, callable $callback): void
    {
        $params['stream'] = true;
        
        $response = $this->client->request('POST', '/chat/completions', [
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
