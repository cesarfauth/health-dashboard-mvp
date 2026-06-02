<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\HealthRecord;
use App\Support\TrendFeatureBuilder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Verifies the deterministic math behind the trend differentiator. Boots the
 * framework only for config(biomarkers); touches no database.
 */
class TrendFeatureBuilderTest extends TestCase
{
    private function record(float $sleep, int $glucose, int $hrv, string $date): HealthRecord
    {
        $record = new HealthRecord([
            'sleep_hours' => $sleep,
            'glucose_level' => $glucose,
            'hrv' => $hrv,
        ]);
        $record->created_at = Carbon::parse($date);

        return $record;
    }

    public function test_it_computes_deltas_direction_and_averages_chronologically(): void
    {
        // Intentionally unordered input to prove it sorts by created_at.
        $records = collect([
            $this->record(5.0, 118, 40, '2026-06-03'),  // newest
            $this->record(8.0, 88, 70, '2026-06-01'),    // oldest
            $this->record(6.5, 98, 55, '2026-06-02'),
        ]);

        $features = (new TrendFeatureBuilder)->build($records);

        $this->assertSame(3, $features['records_analyzed']);

        $sleep = $features['metrics']['sleep_hours'];
        $this->assertSame(8.0, $sleep['first']);
        $this->assertSame(5.0, $sleep['latest']);
        $this->assertSame(6.5, $sleep['average']);
        $this->assertSame(-37.5, $sleep['change_pct']);
        $this->assertSame('down', $sleep['direction']);

        $glucose = $features['metrics']['glucose_level'];
        $this->assertSame('up', $glucose['direction']);
        $this->assertSame(88.0, $glucose['first']);
        $this->assertSame(118.0, $glucose['latest']);
    }

    public function test_small_change_is_reported_as_stable(): void
    {
        $records = collect([
            $this->record(7.0, 90, 60, '2026-06-01'),
            $this->record(7.1, 92, 61, '2026-06-02'),
        ]);

        $features = (new TrendFeatureBuilder)->build($records);

        $this->assertSame('stable', $features['metrics']['sleep_hours']['direction']);
    }
}
