<?php

namespace Nicobleiler\Mistral\Types\Chat;

class Message
{
    public function __construct(
        public readonly string $role,
        public readonly string $content,
        public readonly ?string $name = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            role: $data['role'],
            content: $data['content'],
            name: $data['name'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            'role' => $this->role,
            'content' => $this->content,
        ];

        if ($this->name !== null) {
            $array['name'] = $this->name;
        }

        return $array;
    }
}
