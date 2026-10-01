@props([
    'catalog' => [],
    'defaultModel' => null,
    'mode' => 'default',
    'initialModel' => null,
    'initialModels' => [null, null, null],
])

<div x-data="{ mode: {{ Illuminate\Support\Js::from($mode) }} }" class="space-y-4">
    <input type="hidden" name="mode" :value="mode" />

    <div>
        <p class="mb-2 text-sm font-medium">{{ __('personas.mode_title') }}</p>
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="cursor-pointer rounded-box border p-4 transition" :class="mode === 'default' ? 'border-primary bg-primary/10' : 'border-base-300 hover:bg-base-200'">
                <input type="radio" value="default" x-model="mode" class="peer sr-only" />
                <span class="block text-sm font-semibold">{{ __('personas.mode_default') }}</span>
                <span class="mt-1 block text-xs text-base-content/60">
                    {{ __('personas.mode_default_hint') }}
                    @if ($defaultModel)
                        <strong>{{ $defaultModel }}</strong>
                    @endif
                </span>
            </label>

            <label class="cursor-pointer rounded-box border p-4 transition" :class="mode === 'custom' ? 'border-primary bg-primary/10' : 'border-base-300 hover:bg-base-200'">
                <input type="radio" value="custom" x-model="mode" class="peer sr-only" />
                <span class="block text-sm font-semibold">{{ __('personas.mode_custom') }}</span>
                <span class="mt-1 block text-xs text-base-content/60">{{ __('personas.mode_custom_hint') }}</span>
            </label>

            <label class="cursor-pointer rounded-box border p-4 transition" :class="mode === 'three_models' ? 'border-primary bg-primary/10' : 'border-base-300 hover:bg-base-200'">
                <input type="radio" value="three_models" x-model="mode" class="peer sr-only" />
                <span class="block text-sm font-semibold">{{ __('personas.mode_three') }}</span>
                <span class="mt-1 block text-xs text-base-content/60">{{ __('personas.mode_three_hint') }}</span>
            </label>
        </div>
    </div>

    <div x-show="mode === 'custom'">
        <x-ai-model-selector :catalog="$catalog" :selected="$initialModel" name="ai_model_id" />
    </div>

    <div x-show="mode === 'three_models'" class="space-y-4">
        @for ($i = 0; $i < 3; $i++)
            <div>
                <p class="mb-1 text-sm font-medium">{{ __('personas.models_slot', ['n' => $i + 1]) }}</p>
                <x-ai-model-selector :catalog="$catalog" :selected="$initialModels[$i] ?? null" name="ai_model_ids[]" />
            </div>
        @endfor
    </div>
</div>