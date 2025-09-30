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
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: string,
     *         content: string,
     *         name?: string
     *     }>,
     *     functions?: array<array{
     *         name: string,
     *         description?: string,
     *         parameters?: array
     *     }>,
     *     function_call?: string|array{name: string},
     *     temperature?: float,
     *     top_p?: float,
     *     n?: int,
     *     stream?: bool,
     *     stop?: string|array<string>,
     *     max_tokens?: int,
     *     presence_penalty?: float,
     *     frequency_penalty?: float,
     *     logit_bias?: array<string, float>,
     *     user?: string,
     *     response_format?: array{type: string},
     *     seed?: int,
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description?: string,
     *             parameters?: array
     *         }
     *     }>,
     *     tool_choice?: string|array{type: string, function: array{name: string}}
     * } $params
     * @return array{
     *     id: string,
     *     object: string,
     *     created: int,
     *     model: string,
     *     choices: array<array{
     *         index: int,
     *         message: array{
     *             role: string,
     *             content?: string,
     *             function_call?: array{name: string, arguments: string},
     *             tool_calls?: array<array{
     *                 id: string,
     *                 type: string,
     *                 function: array{name: string, arguments: string}
     *             }>
     *         },
     *         finish_reason?: string
     *     }>,
     *     usage: array{
     *         prompt_tokens: int,
     *         completion_tokens: int,
     *         total_tokens: int
     *     }
     * }
     * @throws GuzzleException
     */
    public function create(array $params): array
    {
        $response = $this->client->request('POST', '/v1/chat/completions', [
            'json' => $params
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Create a streaming chat completion
     *
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: string,
     *         content: string,
     *         name?: string
     *     }>,
     *     functions?: array<array{
     *         name: string,
     *         description?: string,
     *         parameters?: array
     *     }>,
     *     function_call?: string|array{name: string},
     *     temperature?: float,
     *     top_p?: float,
     *     n?: int,
     *     stop?: string|array<string>,
     *     max_tokens?: int,
     *     presence_penalty?: float,
     *     frequency_penalty?: float,
     *     logit_bias?: array<string, float>,
     *     user?: string,
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description?: string,
     *             parameters?: array
     *         }
     *     }>,
     *     tool_choice?: string|array{type: string, function: array{name: string}}
     * } $params
     * @param callable(array{
     *     id: string,
     *     object: string,
     *     created: int,
     *     model: string,
     *     choices: array<array{
     *         index: int,
     *         delta: array{
     *             role?: string,
     *             content?: string,
     *             function_call?: array{name?: string, arguments?: string},
     *             tool_calls?: array<array{
     *                 index?: int,
     *                 id?: string,
     *                 type?: string,
     *                 function?: array{name?: string, arguments?: string}
     *             }>
     *         },
     *         finish_reason?: string
     *     }>
     * }): void $callback
     * @return void
     * @throws GuzzleException
     */
    public function stream(array $params, callable $callback): void
    {
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
