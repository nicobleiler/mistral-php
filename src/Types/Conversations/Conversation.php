<?php

namespace Nicobleiler\Mistral\Types\Conversations;

class Conversation
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $created_at,
        public readonly string $agent_id,
        public readonly ?array $metadata = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            object: $data['object'],
            created_at: $data['created_at'],
            agent_id: $data['agent_id'],
            metadata: $data['metadata'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'object' => $this->object,
            'created_at' => $this->created_at,
            'agent_id' => $this->agent_id,
        ];

        if ($this->metadata !== null) {
            $array['metadata'] = $this->metadata;
        }

        return $array;
    }
}
