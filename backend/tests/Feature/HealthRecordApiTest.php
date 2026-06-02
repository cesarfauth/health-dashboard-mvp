<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;
use App\Services\Integrations\Llm\LlmClientInterface;
use App\Services\Integrations\Llm\LlmPrompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthRecordApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Swap the Claude client for a deterministic fake so the HTTP flow is
        // exercised end-to-end without any network call.
        $this->app->instance(LlmClientInterface::class, new class implements LlmClientInterface
        {
            public function analyze(LlmPrompt $prompt): AiAnalysisResult
            {
                return new AiAnalysisResult(
                    summary: 'Fake analysis for tests.',
                    recommendations: [
                        new RecommendationDTO('A', 'Do A.', 'sleep'),
                        new RecommendationDTO('B', 'Do B.', 'nutrition'),
                        new RecommendationDTO('C', 'Do C.', 'activity'),
                    ],
                    disclaimer: 'Not medical advice.',
                    source: 'claude',
                    model: 'claude-sonnet-4-5',
                    type: $prompt->type,
                );
            }
        });
    }

    public function test_post_creates_record_with_classified_biomarkers_and_recommendation(): void
    {
        $response = $this->postJson('/api/health-records', [
            'sleep_hours' => 5.0,
            'glucose_level' => 130,
            'hrv' => 25,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.biomarkers.sleep_hours.status', 'alert')
            ->assertJsonPath('data.biomarkers.glucose_level.status', 'alert')
            ->assertJsonPath('data.recommendation.source', 'claude')
            ->assertJsonCount(3, 'data.recommendation.recommendations')
            ->assertJsonPath('data.recommendation.type', 'snapshot');

        $this->assertDatabaseHas('health_records', [
            'sleep_hours' => 5.0,
            'glucose_level' => 130,
            'hrv' => 25,
        ]);
        $this->assertDatabaseHas('ai_recommendations', [
            'source' => 'claude',
            'type' => 'snapshot',
        ]);
    }

    public function test_post_fails_validation_with_clear_messages(): void
    {
        $response = $this->postJson('/api/health-records', [
            'sleep_hours' => 99,
            'glucose_level' => 'abc',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sleep_hours', 'glucose_level', 'hrv']);
    }

    public function test_index_returns_recent_history_newest_first(): void
    {
        $this->postJson('/api/health-records', ['sleep_hours' => 7, 'glucose_level' => 90, 'hrv' => 60]);
        $this->postJson('/api/health-records', ['sleep_hours' => 8, 'glucose_level' => 95, 'hrv' => 70]);

        $response = $this->getJson('/api/health-records');

        $response->assertOk()->assertJsonCount(2, 'data');
        // Newest first: the second insert should lead.
        $this->assertSame(8.0, (float) $response->json('data.0.biomarkers.sleep_hours.value'));
    }

    public function test_show_returns_404_for_missing_record(): void
    {
        $this->getJson('/api/health-records/999')->assertNotFound();
    }
}
