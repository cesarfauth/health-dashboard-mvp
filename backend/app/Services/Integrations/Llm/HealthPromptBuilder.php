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
        'Estas informações são geradas apenas para fins gerais de bem-estar e '.
        'educação. Não constituem aconselhamento médico, diagnóstico ou '.
        'tratamento. Consulte sempre um profissional de saúde qualificado sobre '.
        'a sua saúde.';

    private const SYSTEM_RULES =
        'Você é um assistente de bem-estar cauteloso de um app de '.
        'acompanhamento de saúde. Você NUNCA faz diagnóstico, NUNCA cita doenças '.
        'e NUNCA prescreve medicamentos. Você traduz biomarcadores diários em '.
        'hábitos de estilo de vida gentis e práticos. '.
        'Escreva TODOS os textos em português do Brasil. '.
        'Responda APENAS com um único objeto JSON válido, sem markdown e sem '.
        'texto ao redor. '.
        'Esquema: {"summary": string (máx. 2 frases, tom acolhedor, em '.
        'português), "recommendations": [exatamente 3 objetos com '.
        '{"title": string curta em português, "detail": uma frase acionável em '.
        'português, "category": um de "sleep"|"nutrition"|"activity"|"stress"|'.
        '"general"}]}. Os valores de "category" devem permanecer nesses termos '.
        'em inglês; todo o restante em português.';

    public function forSnapshot(HealthRecord $record): LlmPrompt
    {
        $lines = $this->describeBiomarkers([
            'sleep_hours' => $record->sleep_hours,
            'glucose_level' => $record->glucose_level,
            'hrv' => $record->hrv,
        ]);

        $user = "Aqui está um conjunto de leituras de biomarcadores de hoje:\n".
            implode("\n", $lines)."\n\n".
            'Dê um breve resumo acolhedor e exatamente 3 recomendações de hábitos diários.';

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
        $user = 'Aqui estão tendências pré-calculadas dos registros recentes do '.
            "usuário (já calculadas, não recalcule):\n".
            json_encode($features, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            'Interprete essas tendências e dê exatamente 3 recomendações de '.
            'hábitos diários que abordem o padrão mais relevante.';

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
                '- %s: %s %s (situação: %s; faixa saudável %s-%s %s)',
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
