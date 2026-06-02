<?php

declare(strict_types=1);

namespace App\Services\Integrations\Claude;

/**
 * A fully-built prompt ready to be sent to Claude. Keeps prompt construction
 * (HealthPromptBuilder) decoupled from prompt delivery (ClaudeClientInterface).
 */
final readonly class ClaudePrompt
{
    public function __construct(
        public string $system,
        public string $user,
        public string $type = 'snapshot', // 'snapshot' | 'trend'
    ) {}
}
