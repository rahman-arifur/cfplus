<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomContestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_at' => ['nullable', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:600'],
            'include_in_stats' => ['boolean'],
            'is_public' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'contest title',
            'start_at' => 'start time',
            'duration_minutes' => 'duration',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a title for your contest.',
            'duration_minutes.min' => 'Contest duration must be at least 30 minutes.',
            'duration_minutes.max' => 'Contest duration cannot exceed 600 minutes (10 hours).',
            'start_at.after' => 'Contest start time must be in the future.',
        ];
    }
}
