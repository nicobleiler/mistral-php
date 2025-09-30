<?php

namespace Mistral\Types\Chat;

class Choice
{
    public function __construct(
        public readonly int $index,
        public readonly ResponseMessage $message,
        public readonly ?string $finish_reason = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            index: $data['index'],
            message: ResponseMessage::fromArray($data['message']),
            finish_reason: $data['finish_reason'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            'index' => $this->index,
            'message' => $this->message->toArray(),
        ];

        if ($this->finish_reason !== null) {
            $array['finish_reason'] = $this->finish_reason;
        }

        return $array;
    }
}