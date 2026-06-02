<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Validated biomarker input crossing from the HTTP layer into the domain.
 * Built from the FormRequest so the Service never touches the Request object.
 */
final readonly class BiomarkerInputDTO
{
    public function __construct(
        public float $sleepHours,
        public int $glucoseLevel,
        public int $hrv,
        public int $userId,
    ) {}

    /**
     * @param  array{sleep_hours: int|float|string, glucose_level: int|string, hrv: int|string}  $data
     */
    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            sleepHours: (float) $data['sleep_hours'],
            glucoseLevel: (int) $data['glucose_level'],
            hrv: (int) $data['hrv'],
            userId: $userId,
        );
    }

    /** @return array{user_id: int, sleep_hours: float, glucose_level: int, hrv: int} */
    public function toAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'sleep_hours' => $this->sleepHours,
            'glucose_level' => $this->glucoseLevel,
            'hrv' => $this->hrv,
        ];
    }
}
