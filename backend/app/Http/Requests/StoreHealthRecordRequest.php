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
            'sleep_hours.required' => 'Sleep hours are required.',
            'sleep_hours.numeric' => 'Sleep hours must be a number.',
            'sleep_hours.max' => 'Sleep hours cannot exceed 24.',
            'glucose_level.required' => 'Glucose level is required.',
            'glucose_level.integer' => 'Glucose level must be a whole number (mg/dL).',
            'glucose_level.min' => 'Glucose level looks too low to be valid.',
            'glucose_level.max' => 'Glucose level looks too high to be valid.',
            'hrv.required' => 'HRV is required.',
            'hrv.integer' => 'HRV must be a whole number (ms).',
            'hrv.max' => 'HRV looks too high to be valid.',
        ];
    }
}
