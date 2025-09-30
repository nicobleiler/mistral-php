<?php

namespace Mistral\Types\Chat;

class Function_
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?array $parameters = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            parameters: $data['parameters'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = ['name' => $this->name];

        if ($this->description !== null) {
            $array['description'] = $this->description;
        }

        if ($this->parameters !== null) {
            $array['parameters'] = $this->parameters;
        }

        return $array;
    }
}