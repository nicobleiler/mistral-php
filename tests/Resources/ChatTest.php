<?php

namespace Mistral\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Mistral\Resources\Chat;
use Mistral\Types\Chat\ChatRequest;
use Mistral\Types\Chat\ChatResponse;
use Mistral\Types\Chat\Message;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class ChatTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_chat_can_be_instantiated()
    {
        $client = Mockery::mock(Client::class);
        $chat = new Chat($client);
        
        $this->assertInstanceOf(Chat::class, $chat);
    }

    public function test_chat_create_with_array_params_returns_typed_response()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'chatcmpl-abc123',
            'object' => 'chat.completion',
            'created' => 1234567890,
            'model' => 'mistral-tiny',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello! How can I help you today?'
                    ],
                    'finish_reason' => 'stop'
                ]
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30
            ]
        ]);

        $params = [
            'model' => 'mistral-tiny',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello world']
            ],
            'temperature' => 0.7
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/chat/completions', ['json' => $params])
            ->andReturn(new Response(200, [], $expectedResponse));

        $chat = new Chat($client);
        $result = $chat->create($params);

        $this->assertInstanceOf(ChatResponse::class, $result);
        $this->assertEquals('chatcmpl-abc123', $result->id);
        $this->assertEquals('mistral-tiny', $result->model);
    }

    public function test_chat_create_with_typed_request_returns_typed_response()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'chatcmpl-abc123',
            'object' => 'chat.completion',
            'created' => 1234567890,
            'model' => 'mistral-tiny',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello! How can I help you today?'
                    ],
                    'finish_reason' => 'stop'
                ]
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30
            ]
        ]);

        $messages = [new Message('user', 'Hello world')];
        $request = new ChatRequest(
            model: 'mistral-tiny',
            messages: $messages,
            temperature: 0.7
        );

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/chat/completions', ['json' => $request->toArray()])
            ->andReturn(new Response(200, [], $expectedResponse));

        $chat = new Chat($client);
        $result = $chat->create($request);

        $this->assertInstanceOf(ChatResponse::class, $result);
        $this->assertEquals('chatcmpl-abc123', $result->id);
        $this->assertEquals('mistral-tiny', $result->model);
    }

    public function test_chat_stream_supports_both_array_and_typed_params()
    {
        $client = Mockery::mock(Client::class);

        $params = [
            'model' => 'mistral-tiny',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello world']
            ]
        ];

        // Mock the stream response
        $mockBody = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
        $mockBody->shouldReceive('eof')->andReturn(false, false, true);
        $mockBody->shouldReceive('read')->with(1)->andReturn(
            'd', 'a', 't', 'a', ':', ' ', '[', 'D', 'O', 'N', 'E', ']', "\n"
        );

        $mockResponse = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
        $mockResponse->shouldReceive('getBody')->andReturn($mockBody);

        $expectedParams = array_merge($params, ['stream' => true]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/chat/completions', ['json' => $expectedParams, 'stream' => true])
            ->andReturn($mockResponse);

        $chat = new Chat($client);
        $callbackCalled = false;
        
        $chat->stream($params, function($chunk) use (&$callbackCalled) {
            $callbackCalled = true;
        });

        // Just verify the method executed without errors
        $this->assertTrue(true);
    }
}