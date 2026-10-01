<?php

namespace App\Http\Requests;

use App\Enums\AiModelType;
use App\Enums\ApiService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Decode the options JSON textarea into an array before validation.
     */
    protected function prepareForValidation(): void
    {
        $options = $this->input('options');

        if (is_string($options) && trim($options) === '') {
            $this->merge(['options' => null]);

            return;
        }

        if (is_string($options)) {
            $decoded = json_decode($options, true);
            $this->merge(['options' => is_array($decoded) ? $decoded : null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AiModelType::class)],
            'family' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:255'],
            'variant' => ['nullable', 'string', 'max:255'],
            'provider' => ['required', Rule::in(ApiService::mediaValues())],
            'endpoint' => ['required', 'string', 'max:255', Rule::unique('ai_models', 'endpoint')->where(fn ($query) => $query->where('provider', $this->input('provider')))],
            'options' => ['nullable', 'array'],
            'options.size.param' => ['nullable', 'string', 'max:255'],
            'options.size.sizes' => ['nullable', 'array'],
            'options.defaults' => ['nullable', 'array'],
            'enabled' => ['sometimes', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
