#!/usr/bin/env php
<?php
/**
 * Simple MCP test server for integration testing
 * This server implements a minimal MCP protocol without sending notifications
 * that cause issues with the PHP SDK
 */

// Read from stdin, write to stdout
$stdin = fopen('php://stdin', 'r');
$stdout = fopen('php://stdout', 'w');
$stderr = fopen('php://stderr', 'w');

function log_message($message) {
    global $stderr;
    fwrite($stderr, date('Y-m-d H:i:s') . " [TEST_SERVER] " . $message . "\n");
    fflush($stderr);
}

function send_response($id, $result) {
    global $stdout;
    $response = json_encode([
        'jsonrpc' => '2.0',
        'id' => $id,
        'result' => $result
    ]);
    fwrite($stdout, $response . "\n");
    fflush($stdout);
    log_message("Sent response for ID $id");
}

function send_error($id, $code, $message) {
    global $stdout;
    $response = json_encode([
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => [
            'code' => $code,
            'message' => $message
        ]
    ]);
    fwrite($stdout, $response . "\n");
    fflush($stdout);
    log_message("Sent error for ID $id: $message");
}

log_message("Simple MCP test server started");

// Main loop
while (!feof($stdin)) {
    $line = fgets($stdin);
    if ($line === false || trim($line) === '') {
        continue;
    }
    
    $request = json_decode($line, true);
    if ($request === null) {
        log_message("Invalid JSON received");
        continue;
    }
    
    log_message("Received request: " . $request['method'] ?? 'unknown');
    
    $method = $request['method'] ?? '';
    $id = $request['id'] ?? null;
    $params = $request['params'] ?? [];
    
    switch ($method) {
        case 'initialize':
            send_response($id, [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => (object)[],
                    'prompts' => (object)[],
                    'resources' => (object)[]
                ],
                'serverInfo' => [
                    'name' => 'simple-test-server',
                    'version' => '1.0.0'
                ]
            ]);
            break;
            
        case 'initialized':
            // Acknowledge but don't send response for notification
            log_message("Received initialized notification");
            break;
            
        case 'tools/list':
            send_response($id, [
                'tools' => [
                    [
                        'name' => 'echo',
                        'description' => 'Echoes back the input message',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => [
                                    'type' => 'string',
                                    'description' => 'Message to echo'
                                ]
                            ],
                            'required' => ['message']
                        ]
                    ],
                    [
                        'name' => 'add',
                        'description' => 'Adds two numbers',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'a' => ['type' => 'number'],
                                'b' => ['type' => 'number']
                            ],
                            'required' => ['a', 'b']
                        ]
                    ],
                    [
                        'name' => 'get_info',
                        'description' => 'Returns test server information',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => []
                        ]
                    ]
                ]
            ]);
            break;
            
        case 'tools/call':
            $toolName = $params['name'] ?? '';
            $arguments = $params['arguments'] ?? [];
            
            switch ($toolName) {
                case 'echo':
                    send_response($id, [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Echo: ' . ($arguments['message'] ?? '')
                            ]
                        ]
                    ]);
                    break;
                    
                case 'add':
                    $a = $arguments['a'] ?? 0;
                    $b = $arguments['b'] ?? 0;
                    $sum = $a + $b;
                    send_response($id, [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Result: $sum"
                            ]
                        ]
                    ]);
                    break;
                    
                case 'get_info':
                    send_response($id, [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Simple MCP Test Server v1.0.0 - For integration testing'
                            ]
                        ]
                    ]);
                    break;
                    
                default:
                    send_error($id, -32601, "Tool not found: $toolName");
                    break;
            }
            break;
            
        case 'prompts/list':
            send_response($id, [
                'prompts' => []
            ]);
            break;
            
        case 'resources/list':
            send_response($id, [
                'resources' => []
            ]);
            break;
            
        default:
            if ($id !== null) {
                send_error($id, -32601, "Method not found: $method");
            }
            break;
    }
}

log_message("Simple MCP test server stopped");
fclose($stdin);
fclose($stdout);
fclose($stderr);
