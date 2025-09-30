<?php

namespace Mistral\Types\Chat;

class ChatRequest
{
    /** @param Message[] $messages */
    /** @param Function_[]|null $functions */
    /** @param Tool[]|null $tools */
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly ?array $functions = null,
        public readonly string|array|null $function_call = null,
        public readonly ?float $temperature = null,
        public readonly ?float $top_p = null,
        public readonly ?int $n = null,
        public readonly ?bool $stream = null,
        public readonly string|array|null $stop = null,
        public readonly ?int $max_tokens = null,
        public readonly ?float $presence_penalty = null,
        public readonly ?float $frequency_penalty = null,
        public readonly ?array $logit_bias = null,
        public readonly ?string $user = null,
        public readonly ?array $response_format = null,
        public readonly ?int $seed = null,
        public readonly ?array $tools = null,
        public readonly string|array|null $tool_choice = null
    ) {}

    public static function fromArray(array $data): self
    {
        $messages = array_map(fn($msg) => Message::fromArray($msg), $data['messages']);
        
        $functions = null;
        if (isset($data['functions'])) {
            $functions = array_map(fn($func) => Function_::fromArray($func), $data['functions']);
        }

        $tools = null;
        if (isset($data['tools'])) {
            $tools = array_map(fn($tool) => Tool::fromArray($tool), $data['tools']);
        }

        return new self(
            model: $data['model'],
            messages: $messages,
            functions: $functions,
            function_call: $data['function_call'] ?? null,
            temperature: $data['temperature'] ?? null,
            top_p: $data['top_p'] ?? null,
            n: $data['n'] ?? null,
            stream: $data['stream'] ?? null,
            stop: $data['stop'] ?? null,
            max_tokens: $data['max_tokens'] ?? null,
            presence_penalty: $data['presence_penalty'] ?? null,
            frequency_penalty: $data['frequency_penalty'] ?? null,
            logit_bias: $data['logit_bias'] ?? null,
            user: $data['user'] ?? null,
            response_format: $data['response_format'] ?? null,
            seed: $data['seed'] ?? null,
            tools: $tools,
            tool_choice: $data['tool_choice'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            'model' => $this->model,
            'messages' => array_map(fn($msg) => $msg->toArray(), $this->messages),
        ];

        $optionalFields = [
            'functions' => $this->functions ? array_map(fn($func) => $func->toArray(), $this->functions) : null,
            'function_call' => $this->function_call,
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
            'n' => $this->n,
            'stream' => $this->stream,
            'stop' => $this->stop,
            'max_tokens' => $this->max_tokens,
            'presence_penalty' => $this->presence_penalty,
            'frequency_penalty' => $this->frequency_penalty,
            'logit_bias' => $this->logit_bias,
            'user' => $this->user,
            'response_format' => $this->response_format,
            'seed' => $this->seed,
            'tools' => $this->tools ? array_map(fn($tool) => $tool->toArray(), $this->tools) : null,
            'tool_choice' => $this->tool_choice,
        ];

        foreach ($optionalFields as $key => $value) {
            if ($value !== null) {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}