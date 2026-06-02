<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use App\DTOs\AiAnalysisResult;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Decorator that adds resilience to whichever provider is configured: if the
 * provider has no API key or its call fails, it transparently degrades to the
 * deterministic fallback and logs the reason. This keeps the resilience concern
 * in one place (SRP) instead of duplicating it inside every provider.
 */
class ResilientLlmClient implements LlmClientInterface
{
    public function __construct(
        private readonly LlmClientInterface $primary,
        private readonly FallbackLlmService $fallback,
    ) {}

    public function analyze(LlmPrompt $prompt): AiAnalysisResult
    {
        try {
            return $this->primary->analyze($prompt);
        } catch (MissingApiKeyException $e) {
            Log::info('LLM: no API key configured, using deterministic fallback.');
        } catch (Throwable $e) {
            Log::warning('LLM: provider call failed, falling back.', [
                'type' => $prompt->type,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->fallback->analyze($prompt);
    }
}
