<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\AiRecommendation;
use App\Models\HealthRecord;
use App\Repositories\Contracts\HealthRecordRepositoryInterface;
use Illuminate\Support\Collection;

class HealthRecordRepository implements HealthRecordRepositoryInterface
{
    public function create(array $attributes): HealthRecord
    {
        return HealthRecord::create($attributes);
    }

    public function find(int $id): ?HealthRecord
    {
        return HealthRecord::find($id);
    }

    public function findForUser(int $id, int $userId): ?HealthRecord
    {
        return HealthRecord::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function recentForUser(int $userId, int $limit = 10): Collection
    {
        return HealthRecord::query()
            ->where('user_id', $userId)
            ->with('latestRecommendation')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function attachRecommendation(HealthRecord $record, array $attributes): AiRecommendation
    {
        return $record->recommendations()->create($attributes);
    }
}
