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
                    'Add at least %d readings for a reliable trend — you have %d so far.',
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
