<?php

declare(strict_types=1);

namespace App\Services\Integrations\Llm;

use App\Models\HealthRecord;
use App\Support\BiomarkerClassifier;
use Illuminate\Support\Collection;

/**
 * Builds clinical-friendly (NON-diagnostic) prompts and owns the mandatory
 * medical disclaimer. Pure string construction — no I/O — so it is fully
 * unit-testable and keeps prompt engineering in one reviewable place.
 *
 * The system prompt deliberately mentions JSON because OpenAI's
 * response_format=json_object requires the word "json" in the conversation.
 */
class HealthPromptBuilder
{
    /**
     * Mandatory disclaimer attached to every AI output. Defined here (not left
     * to the model) so its presence is guaranteed regardless of LLM behavior.
     */
    public const DISCLAIMER =
        'This information is generated for general wellness and educational '.
        'purposes only. It is not medical advice, diagnosis, or treatment. '.
        'Always consult a qualified healthcare professional about your health.';

    private const SYSTEM_RULES =
        'You are a cautious wellness assistant for a health-tracking app. '.
        'You NEVER diagnose, NEVER name diseases, and NEVER prescribe medication. '.
        'You translate daily biomarkers into gentle, practical lifestyle habits. '.
        'Respond ONLY with a single valid JSON object, no markdown, no prose around it. '.
        'Schema: {"summary": string (max 2 sentences, supportive tone), '.
        '"recommendations": [exactly 3 objects with '.
        '{"title": short string, "detail": one actionable sentence, '.
        '"category": one of "sleep"|"nutrition"|"activity"|"stress"|"general"}]}.';

    public function forSnapshot(HealthRecord $record): LlmPrompt
    {
        $lines = $this->describeBiomarkers([
            'sleep_hours' => $record->sleep_hours,
            'glucose_level' => $record->glucose_level,
            'hrv' => $record->hrv,
        ]);

        $user = "Here is a single set of biomarker readings for today:\n".
            implode("\n", $lines)."\n\n".
            'Give a brief supportive summary and exactly 3 daily-habit recommendations.';

        return new LlmPrompt(self::SYSTEM_RULES, $user, 'snapshot');
    }

    /**
     * Trend prompt: receives pre-computed temporal features (deltas, averages,
     * direction) so the model INTERPRETS rather than calculates.
     *
     * @param  Collection<int, HealthRecord>  $history  newest first
     * @param  array<string, mixed>  $features  deterministic stats computed in PHP
     */
    public function forTrend(Collection $history, array $features): LlmPrompt
    {
        $user = "Here are pre-computed trends from the user's recent records ".
            "(already calculated, do not recompute):\n".
            json_encode($features, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            'Interpret these trends and give exactly 3 daily-habit recommendations '.
            'that address the most relevant pattern.';

        return new LlmPrompt(self::SYSTEM_RULES, $user, 'trend');
    }

    /**
     * @param  array<string, int|float>  $values
     * @return list<string>
     */
    private function describeBiomarkers(array $values): array
    {
        $lines = [];

        foreach ($values as $metric => $value) {
            $config = config("biomarkers.$metric");
            $status = BiomarkerClassifier::classify($metric, $value)->value;

            $lines[] = sprintf(
                '- %s: %s %s (status: %s; healthy range %s-%s %s)',
                $config['label'],
                $value,
                $config['unit'],
                $status,
                $config['normal_min'],
                $config['normal_max'],
                $config['unit'],
            );
        }

        return $lines;
    }
}
