<?php

namespace Mistral\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Mistral\Resources\Chat chat()
 * @method static \Mistral\Resources\Embeddings embeddings()
 * @method static \Mistral\Resources\Models models()
 * @method static array request(string $method, string $endpoint, array $data = [])
 */
class Mistral extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mistral';
    }
}