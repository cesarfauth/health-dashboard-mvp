<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\HealthRecord;
use Illuminate\Support\Collection;

/**
 * Deterministic temporal feature engineering.
 *
 * This is the heart of the "trend analysis" differentiator: ALL numeric work
 * (deltas, averages, percent change, direction) happens here, in PHP, so the
 * figures are exact and reproducible. The LLM later only *interprets* these
 * pre-computed features — it never does arithmetic, which removes the main
 * source of hallucination and keeps the analysis trustworthy.
 *
 * Pure and side-effect free → fully unit-testable.
 */
final class TrendFeatureBuilder
{
    private const METRICS = ['sleep_hours', 'glucose_level', 'hrv'];

    /** Percent-change magnitude under which a metric is considered "stable". */
    private const STABLE_THRESHOLD = 5.0;

    /**
     * @param  Collection<int, HealthRecord>  $records  any order
     * @return array<string, mixed>
     */
    public function build(Collection $records): array
    {
        /** @var Collection<int, HealthRecord> $ordered */
        $ordered = $records->sortBy('created_at')->values();

        $first = $ordered->first();
        $last = $ordered->last();

        return [
            'records_analyzed' => $ordered->count(),
            'period' => [
                'from' => $first?->created_at?->toIso8601String(),
                'to' => $last?->created_at?->toIso8601String(),
                'days_span' => $first && $last
                    ? (int) $first->created_at->diffInDays($last->created_at)
                    : 0,
            ],
            'metrics' => collect(self::METRICS)
                ->mapWithKeys(fn (string $metric) => [
                    $metric => $this->metricFeatures($ordered, $metric),
                ])
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, HealthRecord>  $ordered  oldest -> newest
     * @return array<string, mixed>
     */
    private function metricFeatures(Collection $ordered, string $metric): array
    {
        /** @var Collection<int, float> $values */
        $values = $ordered->map(fn (HealthRecord $r) => (float) $r->{$metric});

        $first = (float) $values->first();
        $latest = (float) $values->last();
        $average = round((float) $values->average(), 2);

        $changePct = $first != 0.0
            ? round((($latest - $first) / abs($first)) * 100, 1)
            : 0.0;

        $config = config("biomarkers.$metric");

        return [
            'label' => $config['label'],
            'unit' => $config['unit'],
            'first' => round($first, 2),
            'latest' => round($latest, 2),
            'average' => $average,
            'min' => round((float) $values->min(), 2),
            'max' => round((float) $values->max(), 2),
            'change_pct' => $changePct,
            'direction' => $this->direction($changePct),
        ];
    }

    private function direction(float $changePct): string
    {
        if ($changePct > self::STABLE_THRESHOLD) {
            return 'up';
        }
        if ($changePct < -self::STABLE_THRESHOLD) {
            return 'down';
        }

        return 'stable';
    }
}
