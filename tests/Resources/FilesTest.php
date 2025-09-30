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
        
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($tempFile, '{"input": "test", "output": "test"}');
        
        $result = $files->upload([
            'file' => $tempFile,
            'purpose' => 'fine-tune'
        ]);
        
        // Clean up temp file
        unlink($tempFile);

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
    
    public function test_files_upload_with_resource_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'file-xyz789',
            'object' => 'file',
            'bytes' => 512,
            'created_at' => 1234567890,
            'filename' => 'resource_file.jsonl',
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
        
        // Create a resource for testing
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, '{"input": "test", "output": "test"}');
        rewind($resource);
        
        $result = $files->upload([
            'file' => $resource,
            'purpose' => 'fine-tune'
        ]);
        
        fclose($resource);

        $this->assertEquals('file-xyz789', $result['id']);
        $this->assertEquals('fine-tune', $result['purpose']);
    }
}