<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\TrendAnalysisResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read TrendAnalysisResult $resource
 */
class TrendAnalysisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = $this->resource;

        if (! $result->sufficient) {
            return [
                'type' => 'trend',
                'status' => 'insufficient_data',
                'records_analyzed' => $result->recordsAnalyzed,
                'required' => $result->required,
                'message' => sprintf(
                    'Adicione pelo menos %d leituras para uma tendência confiável — você tem %d até agora.',
                    $result->required,
                    $result->recordsAnalyzed,
                ),
            ];
        }

        return [
            'type' => 'trend',
            'status' => 'ok',
            'records_analyzed' => $result->recordsAnalyzed,
            'period' => $result->features['period'],
            'metrics' => $result->features['metrics'],
            'recommendation' => new RecommendationResource($result->recommendation),
        ];
    }
}
