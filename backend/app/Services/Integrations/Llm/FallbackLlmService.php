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
            ? 'Visão de tendência básica gerada offline. Configure uma chave de API de LLM para insights personalizados por IA.'
            : 'Aqui está uma base geral de bem-estar para suas leituras. Configure uma chave de API de LLM para insights personalizados por IA.';

        return new AiAnalysisResult(
            summary: $summary,
            recommendations: [
                new RecommendationDTO(
                    title: 'Mantenha um horário de sono regular',
                    detail: 'Busque de 7 a 9 horas e tente dormir e acordar em horários consistentes.',
                    category: 'sleep',
                ),
                new RecommendationDTO(
                    title: 'Equilibre refeições e hidratação',
                    detail: 'Combine carboidratos com proteína e fibras e beba água ao longo do dia.',
                    category: 'nutrition',
                ),
                new RecommendationDTO(
                    title: 'Inclua movimento leve diário',
                    detail: 'Uma caminhada de 20 a 30 minutos ajuda na recuperação e na energia estável.',
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
