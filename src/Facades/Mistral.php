<?php

namespace Nicobleiler\Mistral\Facades;

use Illuminate\Support\Facades\Facade;
use \Nicobleiler\Mistral\Resources\Chat;
use \Nicobleiler\Mistral\Resources\Embeddings;
use \Nicobleiler\Mistral\Resources\Models;
use \Nicobleiler\Mistral\Resources\Files;
use \Nicobleiler\Mistral\Resources\FineTuning;
use \Nicobleiler\Mistral\Resources\Agents;
use \Nicobleiler\Mistral\Resources\Conversations;

/**
 * @method static Chat chat()
 * @method static Embeddings embeddings()
 * @method static Models models()
 * @method static Files files()
 * @method static FineTuning fineTuning()
 * @method static Agents agents()
 * @method static Conversations conversations()
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
