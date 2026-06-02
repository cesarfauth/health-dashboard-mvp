<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use App\DTOs\AiAnalysisResult;

/**
 * Provider-agnostic contract for the LLM. The domain depends on this
 * abstraction, never on a specific vendor SDK — which is what lets us swap the
 * provider (OpenAI <-> Anthropic) via a single config value and mock it in
 * tests.
 */
interface LlmClientInterface
{
    public function analyze(LlmPrompt $prompt): AiAnalysisResult;
}
