<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;

/**
 * Deterministic, offline implementation of the LLM client.
 *
 * Used as a safety net when the configured provider has no API key or its call
 * fails, so the whole product stays demonstrable end-to-end with zero
 * credentials. Every response is transparently flagged source=fallback.
 */
class FallbackLlmService implements LlmClientInterface
{
    public function analyze(LlmPrompt $prompt): AiAnalysisResult
    {
        $summary = $prompt->type === 'trend'
            ? 'Baseline trend view generated offline. Configure an LLM API key for AI-tailored insights.'
            : 'Here is a general wellness baseline for your readings. Configure an LLM API key for AI-tailored insights.';

        return new AiAnalysisResult(
            summary: $summary,
            recommendations: [
                new RecommendationDTO(
                    title: 'Keep a steady sleep schedule',
                    detail: 'Aim for 7-9 hours and try to sleep and wake at consistent times.',
                    category: 'sleep',
                ),
                new RecommendationDTO(
                    title: 'Balance meals and hydration',
                    detail: 'Pair carbs with protein and fiber, and drink water throughout the day.',
                    category: 'nutrition',
                ),
                new RecommendationDTO(
                    title: 'Add light daily movement',
                    detail: 'A 20-30 minute walk supports recovery and steady energy.',
                    category: 'activity',
                ),
            ],
            disclaimer: HealthPromptBuilder::DISCLAIMER,
            source: 'fallback',
            model: null,
            type: $prompt->type,
        );
    }
}
