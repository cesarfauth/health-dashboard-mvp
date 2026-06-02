<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\HealthRecord;
use App\Support\BiomarkerClassifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HealthRecord
 */
class HealthRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'recorded_at' => $this->created_at?->toIso8601String(),
            'biomarkers' => [
                'sleep_hours' => $this->biomarker('sleep_hours', $this->sleep_hours),
                'glucose_level' => $this->biomarker('glucose_level', $this->glucose_level),
                'hrv' => $this->biomarker('hrv', $this->hrv),
            ],
            'recommendation' => $this->whenLoaded(
                'latestRecommendation',
                fn () => $this->latestRecommendation
                    ? new RecommendationResource($this->latestRecommendation)
                    : null,
            ),
        ];
    }

    /**
     * Decorates a raw biomarker value with its unit, label and semantic status
     * (color + label) so the mobile client renders cards without re-deriving
     * the ranges.
     *
     * @return array<string, mixed>
     */
    private function biomarker(string $metric, int|float $value): array
    {
        $config = config("biomarkers.$metric");
        $status = BiomarkerClassifier::classify($metric, $value);

        return [
            'value' => $value,
            'unit' => $config['unit'],
            'label' => $config['label'],
            'status' => $status->value,         // normal | attention | alert
            'status_label' => $status->label(),
            'color' => $status->color(),
        ];
    }
}
