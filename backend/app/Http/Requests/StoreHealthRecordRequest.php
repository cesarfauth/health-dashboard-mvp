<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHealthRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No auth in this MVP; all requests are allowed.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sleep_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'glucose_level' => ['required', 'integer', 'min:20', 'max:600'],
            'hrv' => ['required', 'integer', 'min:1', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sleep_hours.required' => 'As horas de sono são obrigatórias.',
            'sleep_hours.numeric' => 'As horas de sono devem ser um número.',
            'sleep_hours.max' => 'As horas de sono não podem passar de 24.',
            'glucose_level.required' => 'O nível de glicose é obrigatório.',
            'glucose_level.integer' => 'A glicose deve ser um número inteiro (mg/dL).',
            'glucose_level.min' => 'O nível de glicose parece baixo demais para ser válido.',
            'glucose_level.max' => 'O nível de glicose parece alto demais para ser válido.',
            'hrv.required' => 'A HRV é obrigatória.',
            'hrv.integer' => 'A HRV deve ser um número inteiro (ms).',
            'hrv.max' => 'A HRV parece alta demais para ser válida.',
        ];
    }
}
