<?php

namespace Mistral\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Mistral\Resources\FineTuning;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class FineTuningTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_fine_tuning_can_be_instantiated()
    {
        $client = Mockery::mock(Client::class);
        $fineTuning = new FineTuning($client);
        
        $this->assertInstanceOf(FineTuning::class, $fineTuning);
    }

    public function test_fine_tuning_create_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'ft-job-abc123',
            'object' => 'fine_tuning.job',
            'model' => 'mistral-tiny',
            'created_at' => 1234567890,
            'status' => 'pending',
            'training_file' => 'file-abc123',
            'hyperparameters' => [
                'n_epochs' => 4
            ]
        ]);

        $params = [
            'model' => 'mistral-tiny',
            'training_file' => 'file-abc123',
            'hyperparameters' => [
                'n_epochs' => 4
            ]
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/fine_tuning/jobs', ['json' => $params])
            ->andReturn(new Response(200, [], $expectedResponse));

        $fineTuning = new FineTuning($client);
        $result = $fineTuning->create($params);

        $this->assertEquals('ft-job-abc123', $result['id']);
        $this->assertEquals('pending', $result['status']);
    }

    public function test_fine_tuning_list_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'ft-job-abc123',
                    'object' => 'fine_tuning.job',
                    'model' => 'mistral-tiny',
                    'created_at' => 1234567890,
                    'status' => 'completed'
                ]
            ],
            'has_more' => false
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/fine_tuning/jobs', ['query' => []])
            ->andReturn(new Response(200, [], $expectedResponse));

        $fineTuning = new FineTuning($client);
        $result = $fineTuning->list();

        $this->assertEquals('list', $result['object']);
        $this->assertFalse($result['has_more']);
    }

    public function test_fine_tuning_retrieve_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'ft-job-abc123',
            'object' => 'fine_tuning.job',
            'model' => 'mistral-tiny',
            'created_at' => 1234567890,
            'status' => 'completed',
            'training_file' => 'file-abc123'
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', '/v1/fine_tuning/jobs/ft-job-abc123')
            ->andReturn(new Response(200, [], $expectedResponse));

        $fineTuning = new FineTuning($client);
        $result = $fineTuning->retrieve('ft-job-abc123');

        $this->assertEquals('ft-job-abc123', $result['id']);
        $this->assertEquals('completed', $result['status']);
    }

    public function test_fine_tuning_cancel_sends_correct_request()
    {
        $client = Mockery::mock(Client::class);
        $expectedResponse = json_encode([
            'id' => 'ft-job-abc123',
            'object' => 'fine_tuning.job',
            'model' => 'mistral-tiny',
            'created_at' => 1234567890,
            'status' => 'cancelled'
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', '/v1/fine_tuning/jobs/ft-job-abc123/cancel')
            ->andReturn(new Response(200, [], $expectedResponse));

        $fineTuning = new FineTuning($client);
        $result = $fineTuning->cancel('ft-job-abc123');

        $this->assertEquals('cancelled', $result['status']);
    }
}