<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use Anthropic\Client;
use Anthropic\Messages\TextBlock;
use Anthropic\RequestOptions;
use App\DTOs\AiAnalysisResult;
use App\DTOs\RecommendationDTO;
use RuntimeException;

/**
 * Live Anthropic Claude integration via the official anthropic-ai/sdk, isolated
 * behind LlmClientInterface. Kept alongside OpenAiService to demonstrate that
 * swapping the LLM provider is a one-line config change (LLM_PROVIDER). Throws
 * on any failure so the ResilientLlmClient can decide whether to fall back.
 */
class AnthropicService implements LlmClientInterface
{
    public function analyze(LlmPrompt $prompt): AiAnalysisResult
    {
        $config = config('services.anthropic');
        $apiKey = (string) ($config['api_key'] ?? '');

        if ($apiKey === '') {
            throw new MissingApiKeyException('ANTHROPIC_API_KEY is not configured.');
        }

        $model = (string) $config['model'];

        $client = new Client(
            apiKey: $apiKey,
            baseUrl: (string) $config['base_url'],
            requestOptions: RequestOptions::with(
                timeout: (float) $config['timeout'],
                maxRetries: (int) $config['retries'],
            ),
        );

        $message = $client->messages->create(
            maxTokens: (int) $config['max_tokens'],
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
            source: 'anthropic',
            model: $model,
            type: $prompt->type,
        );
    }

    /**
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
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw): array
    {
        $clean = preg_replace('/^```(?:json)?|```$/m', '', trim($raw)) ?? $raw;
        $decoded = json_decode(trim($clean), true);

        if (! is_array($decoded) || ! isset($decoded['summary'], $decoded['recommendations'])) {
            throw new RuntimeException('Anthropic returned an unparseable or incomplete payload.');
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
            throw new RuntimeException('Anthropic returned no usable recommendations.');
        }

        return $recommendations;
    }
}
