<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiRecommendation;
use App\Models\HealthRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiRecommendation>
 */
class AiRecommendationFactory extends Factory
{
    protected $model = AiRecommendation::class;

    public function definition(): array
    {
        return [
            'health_record_id' => HealthRecord::factory(),
            'type' => 'snapshot',
            'summary' => fake()->sentence(),
            'recommendations' => [
                ['title' => 'Sleep earlier', 'detail' => 'Aim for a consistent bedtime.', 'category' => 'sleep'],
                ['title' => 'Hydrate', 'detail' => 'Drink water through the day.', 'category' => 'nutrition'],
                ['title' => 'Move more', 'detail' => 'Take a 20-minute walk.', 'category' => 'activity'],
            ],
            'disclaimer' => 'This is general wellness information, not medical advice.',
            'source' => 'fallback',
            'model' => null,
        ];
    }
}
