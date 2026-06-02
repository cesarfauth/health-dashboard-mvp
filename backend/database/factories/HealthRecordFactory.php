<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HealthRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HealthRecord>
 */
class HealthRecordFactory extends Factory
{
    protected $model = HealthRecord::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'sleep_hours' => fake()->randomFloat(1, 4, 10),
            'glucose_level' => fake()->numberBetween(70, 140),
            'hrv' => fake()->numberBetween(20, 90),
        ];
    }
}
