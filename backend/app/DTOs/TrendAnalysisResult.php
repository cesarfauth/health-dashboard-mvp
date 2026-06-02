<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\AiRecommendation;

/**
 * Outcome of a temporal trend analysis. When there is not enough history the
 * result is "insufficient" and no AI call is made (the honesty gate).
 */
final readonly class TrendAnalysisResult
{
    public function __construct(
        public bool $sufficient,
        public int $recordsAnalyzed,
        public int $required,
        public ?array $features = null,
        public ?AiRecommendation $recommendation = null,
    ) {}

    public static function insufficient(int $recordsAnalyzed, int $required): self
    {
        return new self(false, $recordsAnalyzed, $required);
    }
}
