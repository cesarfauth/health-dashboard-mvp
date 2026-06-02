<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;
use App\Models\HealthRecord;
use App\Services\Integrations\Claude\ClaudeClientInterface;
use App\Services\Integrations\Claude\ClaudePrompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendAnalysisApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(ClaudeClientInterface::class, new class implements ClaudeClientInterface
        {
            public function analyze(ClaudePrompt $prompt): AiAnalysisResult
            {
                return new AiAnalysisResult(
                    summary: 'Fake trend interpretation.',
                    recommendations: [
                        new RecommendationDTO('A', 'Do A.', 'sleep'),
                        new RecommendationDTO('B', 'Do B.', 'stress'),
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

    public function test_it_refuses_trend_with_insufficient_history(): void
    {
        $records = HealthRecord::factory()->count(2)->create(['user_id' => 1]);

        $response = $this->postJson("/api/health-records/{$records->last()->id}/trend-analysis");

        $response->assertOk()
            ->assertJsonPath('data.status', 'insufficient_data')
            ->assertJsonPath('data.required', 3)
            ->assertJsonPath('data.records_analyzed', 2);

        $this->assertDatabaseMissing('ai_recommendations', ['type' => 'trend']);
    }

    public function test_it_returns_trend_with_features_and_recommendation(): void
    {
        $records = HealthRecord::factory()->count(3)->create(['user_id' => 1]);

        $response = $this->postJson("/api/health-records/{$records->last()->id}/trend-analysis");

        $response->assertCreated()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.records_analyzed', 3)
            ->assertJsonPath('data.recommendation.type', 'trend')
            ->assertJsonPath('data.recommendation.source', 'claude')
            ->assertJsonCount(3, 'data.recommendation.recommendations')
            ->assertJsonStructure([
                'data' => [
                    'period' => ['from', 'to', 'days_span'],
                    'metrics' => [
                        'sleep_hours' => ['change_pct', 'direction', 'average'],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('ai_recommendations', ['type' => 'trend']);
    }

    public function test_it_returns_404_for_missing_record(): void
    {
        $this->postJson('/api/health-records/999/trend-analysis')->assertNotFound();
    }
}
