<?php

namespace Nicobleiler\Mistral\Tests\Types\Chat;

use PHPUnit\Framework\TestCase;
use Nicobleiler\Mistral\Types\Chat\ChatRequest;
use Nicobleiler\Mistral\Types\Chat\ChatResponse;
use Nicobleiler\Mistral\Types\Chat\Message;
use Nicobleiler\Mistral\Types\Chat\Choice;
use Nicobleiler\Mistral\Types\Chat\ResponseMessage;
use Nicobleiler\Mistral\Types\Chat\Usage;

class ChatTypesTest extends TestCase
{
    public function test_message_can_be_created_and_converted()
    {
        $message = new Message('user', 'Hello world', 'test-user');

        $this->assertEquals('user', $message->role);
        $this->assertEquals('Hello world', $message->content);
        $this->assertEquals('test-user', $message->name);

        $array = $message->toArray();
        $this->assertEquals([
            'role' => 'user',
            'content' => 'Hello world',
            'name' => 'test-user'
        ], $array);

        $messageFromArray = Message::fromArray($array);
        $this->assertEquals($message->role, $messageFromArray->role);
        $this->assertEquals($message->content, $messageFromArray->content);
        $this->assertEquals($message->name, $messageFromArray->name);
    }

    public function test_chat_request_can_be_created_from_array()
    {
        $data = [
            'model' => 'mistral-tiny',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello world']
            ],
            'temperature' => 0.7,
            'max_tokens' => 100
        ];

        $request = ChatRequest::fromArray($data);

        $this->assertEquals('mistral-tiny', $request->model);
        $this->assertCount(1, $request->messages);
        $this->assertEquals(0.7, $request->temperature);
        $this->assertEquals(100, $request->max_tokens);
        $this->assertInstanceOf(Message::class, $request->messages[0]);
    }

    public function test_chat_request_converts_back_to_array()
    {
        $messages = [new Message('user', 'Hello world')];
        $request = new ChatRequest(
            model: 'mistral-tiny',
            messages: $messages,
            temperature: 0.7,
            max_tokens: 100
        );

        $array = $request->toArray();

        $this->assertEquals('mistral-tiny', $array['model']);
        $this->assertEquals([
            ['role' => 'user', 'content' => 'Hello world']
        ], $array['messages']);
        $this->assertEquals(0.7, $array['temperature']);
        $this->assertEquals(100, $array['max_tokens']);
        $this->assertArrayNotHasKey('tools', $array); // Should not include null values
    }

    public function test_chat_response_can_be_created_from_array()
    {
        $data = [
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
        ];

        $response = ChatResponse::fromArray($data);

        $this->assertEquals('chatcmpl-abc123', $response->id);
        $this->assertEquals('chat.completion', $response->object);
        $this->assertEquals('mistral-tiny', $response->model);
        $this->assertInstanceOf(Choice::class, $response->choices[0]);
        $this->assertInstanceOf(Usage::class, $response->usage);

        $choice = $response->choices[0];
        $this->assertEquals(0, $choice->index);
        $this->assertEquals('stop', $choice->finish_reason);
        $this->assertInstanceOf(ResponseMessage::class, $choice->message);

        $usage = $response->usage;
        $this->assertEquals(10, $usage->prompt_tokens);
        $this->assertEquals(20, $usage->completion_tokens);
        $this->assertEquals(30, $usage->total_tokens);
    }
}
