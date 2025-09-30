<?php

namespace Mistral\Types\Conversations;

class ConversationRequest
{
    public function __construct(
        public readonly string $agent_id,
        public readonly ?array $metadata = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agent_id: $data['agent_id'],
            metadata: $data['metadata'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = ['agent_id' => $this->agent_id];

        if ($this->metadata !== null) {
            $array['metadata'] = $this->metadata;
        }

        return $array;
    }
}