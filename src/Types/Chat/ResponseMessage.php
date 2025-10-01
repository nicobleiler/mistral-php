<?php

namespace Nicobleiler\Mistral\Types\Chat;

class ResponseMessage
{
    /** @param ToolCall[]|null $tool_calls */
    public function __construct(
        public readonly string $role,
        public readonly ?string $content = null,
        public readonly ?FunctionCall $function_call = null,
        public readonly ?array $tool_calls = null
    ) {}

    public static function fromArray(array $data): self
    {
        $function_call = null;
        if (isset($data['function_call'])) {
            $function_call = FunctionCall::fromArray($data['function_call']);
        }

        $tool_calls = null;
        if (isset($data['tool_calls'])) {
            $tool_calls = array_map(fn($call) => ToolCall::fromArray($call), $data['tool_calls']);
        }

        return new self(
            role: $data['role'],
            content: $data['content'] ?? null,
            function_call: $function_call,
            tool_calls: $tool_calls
        );
    }

    public function toArray(): array
    {
        $array = ['role' => $this->role];

        if ($this->content !== null) {
            $array['content'] = $this->content;
        }

        if ($this->function_call !== null) {
            $array['function_call'] = $this->function_call->toArray();
        }

        if ($this->tool_calls !== null) {
            $array['tool_calls'] = array_map(fn($call) => $call->toArray(), $this->tool_calls);
        }

        return $array;
    }
}
