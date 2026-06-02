<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Normalized result of an AI analysis, regardless of whether it came from the
 * live Claude API or the deterministic fallback. This is the single shape the
 * Service persists and the Resources serialize.
 */
final readonly class AiAnalysisResult
{
    /**
     * @param  list<RecommendationDTO>  $recommendations
     */
    public function __construct(
        public string $summary,
        public array $recommendations,
        public string $disclaimer,
        public string $source,   // 'claude' | 'fallback'
        public ?string $model,
        public string $type = 'snapshot', // 'snapshot' | 'trend'
    ) {}

    /**
     * Maps to the ai_recommendations table columns.
     *
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        return [
            'type' => $this->type,
            'summary' => $this->summary,
            'recommendations' => array_map(
                fn (RecommendationDTO $r) => $r->toArray(),
                $this->recommendations,
            ),
            'disclaimer' => $this->disclaimer,
            'source' => $this->source,
            'model' => $this->model,
        ];
    }
}
