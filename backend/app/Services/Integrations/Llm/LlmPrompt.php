<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

/**
 * A fully-built prompt ready to be sent to any LLM provider. Keeps prompt
 * construction (HealthPromptBuilder) decoupled from prompt delivery
 * (LlmClientInterface implementations).
 */
final readonly class LlmPrompt
{
    public function __construct(
        public string $system,
        public string $user,
        public string $type = 'snapshot', // 'snapshot' | 'trend'
    ) {}
}
