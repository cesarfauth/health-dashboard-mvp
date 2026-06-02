<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AiRecommendationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    /** @use HasFactory<AiRecommendationFactory> */
    use HasFactory;

    protected $fillable = [
        'health_record_id',
        'type',
        'summary',
        'recommendations',
        'disclaimer',
        'source',
        'model',
    ];

    protected $casts = [
        'recommendations' => 'array',
    ];

    /** @return BelongsTo<HealthRecord, $this> */
    public function healthRecord(): BelongsTo
    {
        return $this->belongsTo(HealthRecord::class);
    }
}
