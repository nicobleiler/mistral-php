<?php

namespace Mistral\Types\Chat;

class ChatResponse
{
    /** @param Choice[] $choices */
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $created,
        public readonly string $model,
        public readonly array $choices,
        public readonly Usage $usage
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            object: $data['object'],
            created: $data['created'],
            model: $data['model'],
            choices: array_map(fn($choice) => Choice::fromArray($choice), $data['choices']),
            usage: Usage::fromArray($data['usage'])
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'object' => $this->object,
            'created' => $this->created,
            'model' => $this->model,
            'choices' => array_map(fn($choice) => $choice->toArray(), $this->choices),
            'usage' => $this->usage->toArray(),
        ];
    }
}