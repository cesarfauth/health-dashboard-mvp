<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\AiAnalysisResult;
use App\DTOs\BiomarkerInputDTO;
use App\DTOs\RecommendationDTO;
use App\Models\AiRecommendation;
use App\Models\HealthRecord;
use App\Repositories\Contracts\HealthRecordRepositoryInterface;
use App\Services\HealthRecordService;
use App\Services\Integrations\Llm\HealthPromptBuilder;
use App\Services\Integrations\Llm\LlmClientInterface;
use App\Services\Integrations\Llm\LlmPrompt;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit test of the orchestration logic. The repository, the Claude client
 * and the prompt builder are all mocked, so this test boots no framework and
 * touches no database or network — it asserts ONLY the Service's behavior.
 */
class HealthRecordServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_persists_the_record_runs_analysis_and_attaches_recommendation(): void
    {
        $input = new BiomarkerInputDTO(
            sleepHours: 7.5,
            glucoseLevel: 92,
            hrv: 60,
            userId: 1,
        );

        $record = new HealthRecord($input->toAttributes());
        $record->id = 10;

        $analysis = new AiAnalysisResult(
            summary: 'Solid baseline today.',
            recommendations: [
                new RecommendationDTO('Sleep', 'Keep 7-9h.', 'sleep'),
                new RecommendationDTO('Hydrate', 'Drink water.', 'nutrition'),
                new RecommendationDTO('Walk', 'Move 20 min.', 'activity'),
            ],
            disclaimer: 'Not medical advice.',
            source: 'claude',
            model: 'claude-sonnet-4-5',
        );

        $persisted = new AiRecommendation($analysis->toPersistenceArray());

        // Repository: create() returns our record; attachRecommendation() is
        // called with the exact persistence payload and returns the saved row.
        $repository = Mockery::mock(HealthRecordRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->with($input->toAttributes())
            ->andReturn($record);
        $repository->shouldReceive('attachRecommendation')
            ->once()
            ->with($record, $analysis->toPersistenceArray())
            ->andReturn($persisted);

        // Prompt builder: returns a deterministic prompt for the record.
        $prompt = new LlmPrompt('system', 'user', 'snapshot');
        $prompts = Mockery::mock(HealthPromptBuilder::class);
        $prompts->shouldReceive('forSnapshot')
            ->once()
            ->with($record)
            ->andReturn($prompt);

        // Claude client: receives that prompt, returns the analysis.
        $claude = Mockery::mock(LlmClientInterface::class);
        $claude->shouldReceive('analyze')
            ->once()
            ->with($prompt)
            ->andReturn($analysis);

        $service = new HealthRecordService($repository, $claude, $prompts);

        $result = $service->createWithAnalysis($input);

        $this->assertSame($record, $result);
        $this->assertTrue($result->relationLoaded('latestRecommendation'));
        $this->assertSame($persisted, $result->getRelation('latestRecommendation'));
    }

    public function test_history_delegates_to_repository_with_user_and_limit(): void
    {
        $repository = Mockery::mock(HealthRecordRepositoryInterface::class);
        $repository->shouldReceive('recentForUser')
            ->once()
            ->with(1, 5)
            ->andReturn(collect([new HealthRecord]));

        $service = new HealthRecordService(
            $repository,
            Mockery::mock(LlmClientInterface::class),
            Mockery::mock(HealthPromptBuilder::class),
        );

        $this->assertCount(1, $service->history(1, 5));
    }
}
