<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkCfAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'handle' => [
                'required',
                'string',
                'min:3',
                'max:24',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('cf_accounts', 'handle')
                    ->ignore($this->user()->cfAccount?->id),
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'handle.regex' => 'The handle may only contain letters, numbers, underscores, and hyphens.',
            'handle.unique' => 'This Codeforces handle is already linked to another account.',
        ];
    }
}
