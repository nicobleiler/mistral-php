<?php

namespace Mistral\Types\Chat;

class FunctionCall
{
    public function __construct(
        public readonly string $name,
        public readonly string $arguments
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            arguments: $data['arguments']
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
        ];
    }
}