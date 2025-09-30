<?php

namespace Mistral\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Mistral\Resources\Files;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class FilesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_files_can_be_instantiated()
    {
        $client = Mockery::mock(Client::class);
        $files = new Files($client);
        
        $this->assertInstanceOf(Files::class, $files);
    }

    public function test_files_upload_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'file-abc123',
            'object' => 'file',
            'bytes' => 1024,
            'created_at' => 1234567890,
            'filename' => 'test.jsonl',
            'purpose' => 'fine-tune'
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/files', Mockery::on(function ($options) {
                return isset($options['multipart']) && 
                       count($options['multipart']) === 2;
            }))
            ->andReturn(new Response(200, [], $expectedResponse));

        $files = new Files($client);
        $result = $files->upload([
            'file' => 'test.jsonl',
            'purpose' => 'fine-tune'
        ]);

        $this->assertEquals('file-abc123', $result['id']);
        $this->assertEquals('fine-tune', $result['purpose']);
    }

    public function test_files_list_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'file-abc123',
                    'object' => 'file',
                    'bytes' => 1024,
                    'created_at' => 1234567890,
                    'filename' => 'test.jsonl',
                    'purpose' => 'fine-tune'
                ]
            ]
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/files', ['query' => []])
            ->andReturn(new Response(200, [], $expectedResponse));

        $files = new Files($client);
        $result = $files->list();

        $this->assertEquals('list', $result['object']);
        $this->assertCount(1, $result['data']);
    }

    public function test_files_retrieve_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'file-abc123',
            'object' => 'file',
            'bytes' => 1024,
            'created_at' => 1234567890,
            'filename' => 'test.jsonl',
            'purpose' => 'fine-tune'
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/files/file-abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $files = new Files($client);
        $result = $files->retrieve('file-abc123');

        $this->assertEquals('file-abc123', $result['id']);
    }

    public function test_files_delete_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'file-abc123',
            'object' => 'file',
            'deleted' => true
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('DELETE', '/v1/files/file-abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $files = new Files($client);
        $result = $files->delete('file-abc123');

        $this->assertTrue($result['deleted']);
    }
}