<?php

namespace Mistral\Types\Chat;

class ToolCall
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly FunctionCall $function
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: $data['type'],
            function: FunctionCall::fromArray($data['function'])
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'function' => $this->function->toArray(),
        ];
    }
}