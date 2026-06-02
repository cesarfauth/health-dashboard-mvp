<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\BiomarkerInputDTO;
use App\Models\HealthRecord;
use App\Repositories\Contracts\HealthRecordRepositoryInterface;
use App\Services\Integrations\Llm\HealthPromptBuilder;
use App\Services\Integrations\Llm\LlmClientInterface;
use Illuminate\Support\Collection;

/**
 * Business logic for health records. Orchestrates persistence (repository) and
 * the AI analysis (Claude client) behind their interfaces — no Eloquent and no
 * SDK calls leak in here, which is exactly what makes this class unit-testable
 * with mocks (see tests/Unit/HealthRecordServiceTest).
 */
class HealthRecordService
{
    public function __construct(
        private readonly HealthRecordRepositoryInterface $repository,
        private readonly LlmClientInterface $llm,
        private readonly HealthPromptBuilder $prompts,
    ) {}

    /**
     * Persist a new reading, run a snapshot AI analysis, store and attach it.
     */
    public function createWithAnalysis(BiomarkerInputDTO $input): HealthRecord
    {
        $record = $this->repository->create($input->toAttributes());

        $prompt = $this->prompts->forSnapshot($record);
        $result = $this->llm->analyze($prompt);

        $recommendation = $this->repository->attachRecommendation($record, $result->toPersistenceArray());

        // Expose the just-created recommendation without an extra query.
        $record->setRelation('latestRecommendation', $recommendation);

        return $record;
    }

    /**
     * @return Collection<int, HealthRecord>
     */
    public function history(int $userId, int $limit = 10): Collection
    {
        return $this->repository->recentForUser($userId, $limit);
    }

    public function find(int $id, int $userId): ?HealthRecord
    {
        return $this->repository->findForUser($id, $userId)?->load('latestRecommendation');
    }
}
