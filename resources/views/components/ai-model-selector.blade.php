@props([
    'catalog' => [],
    'selected' => null,
    'name' => 'ai_model_id',
    'id' => 'ai_model_id',
])

<div
    x-data="aiModelSelector({{ Illuminate\Support\Js::from($catalog) }}, {{ Illuminate\Support\Js::from($selected) }})"
    class="grid gap-4 sm:grid-cols-3"
>
    <input type="hidden" name="{{ $name }}" :value="modelId" />

    <div class="space-y-1.5" x-show="families.length > 1">
        <x-forms.label>{{ __('personas.family') }}</x-forms.label>
        <select
            x-model="familyKey"
            @change="onFamily($el.value)"
            class="select select-bordered w-full"
        >
            <template x-for="f in families" :key="f.key">
                <option :value="f.key" :selected="f.key === familyKey" x-text="f.label"></option>
            </template>
        </select>
    </div>

    <div class="space-y-1.5" x-show="families.length > 1 && versions.length > 1">
        <x-forms.label>{{ __('personas.version') }}</x-forms.label>
        <select
            x-model="versionKey"
            @change="onVersion($el.value)"
            class="select select-bordered w-full"
        >
            <template x-for="v in versions" :key="v.key">
                <option :value="v.key" :selected="v.key === versionKey" x-text="v.label"></option>
            </template>
        </select>
    </div>

    <div class="space-y-1.5" x-show="families.length > 1 && versions.length > 1 && variants.length > 1">
        <x-forms.label>{{ __('personas.variant') }}</x-forms.label>
        <select
            x-model="variantKey"
            @change="onVariant($el.value)"
            class="select select-bordered w-full"
        >
            <template x-for="v in variants" :key="v.key">
                <option :value="v.key" :selected="v.key === variantKey" x-text="v.label"></option>
            </template>
        </select>
    </div>
</div>