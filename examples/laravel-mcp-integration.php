<?php

/**
 * Laravel MCP Integration Example
 * 
 * This example shows how to integrate Mistral AI MCP server
 * in a Laravel application.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Controller;
use Illuminate\Http\Request;
use Mistral\Facades\Mistral;
use Mistral\Facades\MistralMcp;
use Mistral\Mcp\MistralMcpServer;

class MistralMcpController extends Controller
{
    /**
     * Get MCP server information
     */
    public function getServerInfo()
    {
        try {
            $mcpServer = app(MistralMcpServer::class);
            $elements = $mcpServer->getElements();
            
            return response()->json([
                'models_info' => $elements->getModelsInfo(),
                'client_config' => $elements->getClientConfig(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get MCP server info: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Start MCP server with HTTP transport
     */
    public function startMcpServer(Request $request)
    {
        $host = $request->get('host', '127.0.0.1');
        $port = $request->get('port', 8080);
        
        try {
            // Note: In a real application, you'd want to run this in a background process
            // This is just for demonstration
            $mcpServer = app(MistralMcpServer::class);
            
            return response()->json([
                'message' => "MCP server would start on {$host}:{$port}",
                'command' => "php artisan mistral:mcp-server --host={$host} --port={$port}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to start MCP server: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test MCP tools functionality
     */
    public function testMcpTools(Request $request)
    {
        try {
            $mcpServer = app(MistralMcpServer::class);
            $elements = $mcpServer->getElements();
            
            $results = [];
            
            // Test list models (doesn't require API key for error handling)
            $results['list_models'] = $elements->listModels();
            
            // Test get model info
            $modelId = $request->get('model', 'mistral-tiny');
            $results['get_model'] = $elements->getModel($modelId);
            
            return response()->json([
                'status' => 'success',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to test MCP tools: ' . $e->getMessage()
            ], 500);
        }
    }
}

/**
 * Example Artisan Command for MCP Server
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mistral\Mcp\MistralMcpServer;

class StartMcpServerCommand extends Command
{
    protected $signature = 'mistral:mcp-server 
                           {--transport=stdio : Transport method (stdio or http)}
                           {--host=127.0.0.1 : HTTP host}
                           {--port=8080 : HTTP port}';
    
    protected $description = 'Start Mistral AI MCP server';
    
    public function handle()
    {
        $transport = $this->option('transport');
        $host = $this->option('host');
        $port = (int) $this->option('port');
        
        $this->info('Starting Mistral AI MCP server...');
        
        try {
            $mcpServer = app(MistralMcpServer::class);
            
            if ($transport === 'http') {
                $this->info("Starting HTTP server on {$host}:{$port}");
                $mcpServer->runHttp($host, $port);
            } else {
                $this->info('Starting STDIO server');
                $mcpServer->runStdio();
            }
        } catch (\Exception $e) {
            $this->error('Failed to start MCP server: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

/**
 * Routes (add to web.php or api.php)
 */

/*
Route::prefix('mistral/mcp')->group(function () {
    Route::get('info', [MistralMcpController::class, 'getServerInfo']);
    Route::post('start', [MistralMcpController::class, 'startMcpServer']);
    Route::post('test', [MistralMcpController::class, 'testMcpTools']);
});
*/

/**
 * Usage in Blade views
 */

/*
@php
    use Mistral\Facades\MistralMcp;
    
    $mcpServer = app(\Mistral\Mcp\MistralMcpServer::class);
    $elements = $mcpServer->getElements();
    $modelsInfo = $elements->getModelsInfo();
@endphp

<div class="mistral-mcp-info">
    <h3>Available Mistral Models</h3>
    <ul>
        @foreach($modelsInfo['models'] as $model)
            <li>
                <strong>{{ $model['id'] }}</strong>: {{ $model['description'] }}
                <br>
                <small>Capabilities: {{ implode(', ', $model['capabilities']) }}</small>
            </li>
        @endforeach
    </ul>
</div>
*/