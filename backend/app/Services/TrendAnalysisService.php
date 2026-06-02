<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TrendAnalysisResult;
use App\Models\HealthRecord;
use App\Repositories\Contracts\HealthRecordRepositoryInterface;
use App\Services\Integrations\Claude\ClaudeClientInterface;
use App\Services\Integrations\Claude\HealthPromptBuilder;
use App\Support\TrendFeatureBuilder;

/**
 * The differentiator. Orchestrates temporal trend analysis:
 *   1. Honesty gate — refuse to "see trends" without enough history.
 *   2. Deterministic feature engineering (TrendFeatureBuilder, pure PHP).
 *   3. AI interpretation of those pre-computed features (never raw math).
 *   4. Persist the trend recommendation against the record.
 */
class TrendAnalysisService
{
    public function __construct(
        private readonly HealthRecordRepositoryInterface $repository,
        private readonly ClaudeClientInterface $claude,
        private readonly HealthPromptBuilder $prompts,
        private readonly TrendFeatureBuilder $features,
    ) {}

    public function analyzeForRecord(HealthRecord $record, int $userId): TrendAnalysisResult
    {
        $required = (int) config('health.trend_min_records');
        $window = (int) config('health.trend_window');

        $records = $this->repository->recentForUser($userId, $window);

        // Honesty gate: not enough data points to claim a meaningful trend.
        if ($records->count() < $required) {
            return TrendAnalysisResult::insufficient($records->count(), $required);
        }

        $features = $this->features->build($records);

        $prompt = $this->prompts->forTrend($records, $features);
        $analysis = $this->claude->analyze($prompt);

        $recommendation = $this->repository->attachRecommendation(
            $record,
            $analysis->toPersistenceArray(),
        );

        return new TrendAnalysisResult(
            sufficient: true,
            recordsAnalyzed: $records->count(),
            required: $required,
            features: $features,
            recommendation: $recommendation,
        );
    }
}
