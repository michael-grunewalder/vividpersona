<?php

namespace App\Http\Requests;

use App\Enums\ApiProviderType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveApiProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation, dropping credential field rows that
     * the admin left completely empty.
     */
    protected function prepareForValidation(): void
    {
        $meta = collect($this->input('meta', []))
            ->filter(fn (array $entry) => filled($entry['name'] ?? null) || filled($entry['description'] ?? null))
            ->values()
            ->all();

        $this->merge(['meta' => $meta]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ApiProviderType::class)],
            'machine_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('api_providers', 'machine_name')->ignore($this->route('api_provider')),
            ],
            'friendly_name' => ['required', 'string', 'max:255'],
            'base_url' => ['nullable', 'string', 'max:255', 'url'],
            'meta' => ['nullable', 'array'],
            'meta.*.name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_-]+$/', 'distinct'],
            'meta.*.description' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for the credential field definitions.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'meta.*.name.required' => __('admin.providers.meta_name_required'),
            'meta.*.name.regex' => __('admin.providers.meta_name_invalid'),
            'meta.*.name.distinct' => __('admin.providers.meta_name_duplicate'),
            'meta.*.description.required' => __('admin.providers.meta_description_required'),
        ];
    }
}
