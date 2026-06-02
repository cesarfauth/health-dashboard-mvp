<?php

declare(strict_types=1);

namespace App\Services\Integrations\Claude;

use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;

/**
 * Deterministic, offline implementation of the Claude client.
 *
 * Used when no ANTHROPIC_API_KEY is configured, or as a safety net when the
 * live API call fails. It keeps the whole product demonstrable end-to-end with
 * zero credentials, and every response is transparently flagged source=fallback.
 */
class FallbackClaudeService implements ClaudeClientInterface
{
    public function analyze(ClaudePrompt $prompt): AiAnalysisResult
    {
        $summary = $prompt->type === 'trend'
            ? 'Baseline trend view generated offline. Connect an API key for AI-tailored insights.'
            : 'Here is a general wellness baseline for your readings. Connect an API key for AI-tailored insights.';

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
