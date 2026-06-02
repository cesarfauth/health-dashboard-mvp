<?php

declare(strict_types=1);

namespace App\Services\Integrations\Claude;

use Anthropic\Client;
use Anthropic\Messages\TextBlock;
use Anthropic\RequestOptions;
use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Live Claude integration using the official anthropic-ai/sdk, isolated behind
 * ClaudeClientInterface.
 *
 * Resilience: if no API key is configured, or the live call/parse fails, it
 * gracefully degrades to the deterministic FallbackClaudeService and logs the
 * reason — the caller always gets a well-formed AiAnalysisResult.
 */
class ClaudeService implements ClaudeClientInterface
{
    public function __construct(
        private readonly FallbackClaudeService $fallback,
    ) {}

    public function analyze(ClaudePrompt $prompt): AiAnalysisResult
    {
        $apiKey = (string) config('services.anthropic.api_key');

        if ($apiKey === '') {
            Log::info('Claude: no API key configured, using deterministic fallback.');

            return $this->fallback->analyze($prompt);
        }

        try {
            return $this->callClaude($apiKey, $prompt);
        } catch (Throwable $e) {
            Log::warning('Claude: live call failed, falling back.', [
                'type' => $prompt->type,
                'error' => $e->getMessage(),
            ]);

            return $this->fallback->analyze($prompt);
        }
    }

    private function callClaude(string $apiKey, ClaudePrompt $prompt): AiAnalysisResult
    {
        $model = (string) config('services.anthropic.model');

        $client = new Client(
            apiKey: $apiKey,
            baseUrl: (string) config('services.anthropic.base_url'),
            requestOptions: RequestOptions::with(
                timeout: (float) config('services.anthropic.timeout'),
                maxRetries: (int) config('services.anthropic.retries'),
            ),
        );

        $message = $client->messages->create(
            maxTokens: (int) config('services.anthropic.max_tokens'),
            messages: [['role' => 'user', 'content' => $prompt->user]],
            model: $model,
            system: $prompt->system,
            temperature: 0.5,
        );

        $payload = $this->decodeJson($this->extractText($message->content));

        return new AiAnalysisResult(
            summary: trim((string) ($payload['summary'] ?? '')),
            recommendations: $this->parseRecommendations($payload['recommendations'] ?? []),
            disclaimer: HealthPromptBuilder::DISCLAIMER,
            source: 'claude',
            model: $model,
            type: $prompt->type,
        );
    }

    /**
     * Concatenates all text blocks from the response content.
     *
     * @param  array<int, object>  $content
     */
    private function extractText(array $content): string
    {
        $text = '';

        foreach ($content as $block) {
            if ($block instanceof TextBlock) {
                $text .= $block->text;
            }
        }

        return trim($text);
    }

    /**
     * Tolerates models that wrap JSON in markdown fences.
     *
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw): array
    {
        $clean = trim($raw);
        $clean = preg_replace('/^```(?:json)?|```$/m', '', $clean) ?? $clean;

        $decoded = json_decode(trim($clean), true);

        if (! is_array($decoded) || ! isset($decoded['summary'], $decoded['recommendations'])) {
            throw new \RuntimeException('Claude returned an unparseable or incomplete payload.');
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
            throw new \RuntimeException('Claude returned no usable recommendations.');
        }

        return $recommendations;
    }
}
