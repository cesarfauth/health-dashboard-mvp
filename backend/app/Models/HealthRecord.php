<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HealthRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HealthRecord extends Model
{
    /** @use HasFactory<HealthRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sleep_hours',
        'glucose_level',
        'hrv',
    ];

    protected $casts = [
        'sleep_hours' => 'float',
        'glucose_level' => 'integer',
        'hrv' => 'integer',
    ];

    /** @return HasMany<AiRecommendation, $this> */
    public function recommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }

    /** The most recent AI recommendation for this record (any type). */
    public function latestRecommendation(): HasOne
    {
        return $this->hasOne(AiRecommendation::class)->latestOfMany();
    }
}
