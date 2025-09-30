<?php

namespace Mistral\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void runStdio()
 * @method static void runHttp(string $host = '127.0.0.1', int $port = 8080)
 * @method static \Mcp\Server getServer()
 * @method static \Mistral\Mcp\MistralMcpElements getElements()
 */
class MistralMcp extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mistral.mcp';
    }
}