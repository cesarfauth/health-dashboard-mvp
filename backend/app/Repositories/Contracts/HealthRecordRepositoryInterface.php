<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\AiRecommendation;
use App\Models\HealthRecord;
use Illuminate\Support\Collection;

/**
 * Persistence contract for health records. The Service layer depends on this
 * abstraction (DIP), never on Eloquent directly — which is what makes the
 * Service unit-testable with a mock repository.
 */
interface HealthRecordRepositoryInterface
{
    public function create(array $attributes): HealthRecord;

    public function find(int $id): ?HealthRecord;

    /**
     * Find a record scoped to a user (prevents cross-user access once auth
     * exists). Returns null if not found / not owned.
     */
    public function findForUser(int $id, int $userId): ?HealthRecord;

    /**
     * Most recent records for a user, newest first, with their latest
     * recommendation eager-loaded for dashboard/history rendering.
     *
     * @return Collection<int, HealthRecord>
     */
    public function recentForUser(int $userId, int $limit = 10): Collection;

    /**
     * Persist an AI recommendation attached to a record.
     */
    public function attachRecommendation(HealthRecord $record, array $attributes): AiRecommendation;
}
