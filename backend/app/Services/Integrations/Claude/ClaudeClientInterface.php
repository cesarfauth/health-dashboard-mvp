<?php

declare(strict_types=1);

namespace App\Services\Integrations\Claude;

use App\DTOs\AiAnalysisResult;

/**
 * Integration contract for the LLM. The domain depends on this abstraction,
 * never on the Anthropic SDK directly — which lets us swap the implementation
 * (real SDK <-> deterministic fallback) and mock it in tests.
 */
interface ClaudeClientInterface
{
    public function analyze(ClaudePrompt $prompt): AiAnalysisResult;
}
