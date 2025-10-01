<?php

namespace Nicobleiler\Mistral\Types\Chat;

class Tool
{
    public function __construct(
        public readonly string $type,
        public readonly Function_ $function
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            function: Function_::fromArray($data['function'])
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'function' => $this->function->toArray(),
        ];
    }
}
