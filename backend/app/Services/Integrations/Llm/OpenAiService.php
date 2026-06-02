<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Live OpenAI integration via Laravel's HTTP client (Chat Completions API with
 * JSON mode). Isolated behind LlmClientInterface. Throws on any failure so the
 * ResilientLlmClient can decide whether to fall back.
 */
class OpenAiService implements LlmClientInterface
{
    public function analyze(LlmPrompt $prompt): AiAnalysisResult
    {
        $config = config('services.openai');
        $apiKey = (string) ($config['api_key'] ?? '');

        if ($apiKey === '') {
            throw new MissingApiKeyException('OPENAI_API_KEY is not configured.');
        }

        $model = (string) $config['model'];

        $response = Http::baseUrl((string) $config['base_url'])
            ->withToken($apiKey)
            ->timeout((int) $config['timeout'])
            ->retry((int) $config['retries'], 250)
            ->post('/chat/completions', [
                'model' => $model,
                'max_tokens' => (int) $config['max_tokens'],
                'temperature' => 0.5,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $prompt->system],
                    ['role' => 'user', 'content' => $prompt->user],
                ],
            ])
            ->throw();

        $content = (string) $response->json('choices.0.message.content', '');
        $payload = $this->decodeJson($content);

        return new AiAnalysisResult(
            summary: trim((string) ($payload['summary'] ?? '')),
            recommendations: $this->parseRecommendations($payload['recommendations'] ?? []),
            disclaimer: HealthPromptBuilder::DISCLAIMER,
            source: 'openai',
            model: (string) $response->json('model', $model),
            type: $prompt->type,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw): array
    {
        $decoded = json_decode(trim($raw), true);

        if (! is_array($decoded) || ! isset($decoded['summary'], $decoded['recommendations'])) {
            throw new RuntimeException('OpenAI returned an unparseable or incomplete payload.');
        }

        return $decoded;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<RecommendationDTO>
     */
    private function parseRecommendations(array $items): array
    {
        $recommendations = [];

        foreach (array_slice($items, 0, 3) as $item) {
            if (is_array($item)) {
                $recommendations[] = RecommendationDTO::fromArray($item);
            }
        }

        if (count($recommendations) === 0) {
            throw new RuntimeException('OpenAI returned no usable recommendations.');
        }

        return $recommendations;
    }
}
