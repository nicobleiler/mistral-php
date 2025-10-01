<?php

namespace Nicobleiler\Mistral\Types\Chat;

class Usage
{
    public function __construct(
        public readonly int $prompt_tokens,
        public readonly int $completion_tokens,
        public readonly int $total_tokens
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt_tokens: $data['prompt_tokens'],
            completion_tokens: $data['completion_tokens'],
            total_tokens: $data['total_tokens']
        );
    }

    public function toArray(): array
    {
        return [
            'prompt_tokens' => $this->prompt_tokens,
            'completion_tokens' => $this->completion_tokens,
            'total_tokens' => $this->total_tokens,
        ];
    }
}
