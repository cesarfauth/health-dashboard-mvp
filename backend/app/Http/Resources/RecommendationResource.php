<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AiRecommendation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiRecommendation
 */
class RecommendationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'summary' => $this->summary,
            'recommendations' => $this->recommendations,
            'disclaimer' => $this->disclaimer,
            'source' => $this->source, // 'claude' | 'fallback'
            'model' => $this->model,
            'generated_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
