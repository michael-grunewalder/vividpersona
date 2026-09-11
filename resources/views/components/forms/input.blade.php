@props([
    'name',
    'type' => 'text',
    'value' => '',
    'label' => null,
    'error' => null,
    'hint' => null,
])

<div class="space-y-1.5">
    @if ($label)
        <x-forms.label for="{{ $name }}">{{ $label }}</x-forms.label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        {{ $attributes->merge(['class' => 'input input-bordered w-full '.($error ? 'input-error' : '')]) }}
    />

    @if ($hint)
        <p class="mt-1 text-xs text-base-content/50">{{ $hint }}</p>
    @endif

    @if ($error)
        <x-forms.error>{{ $error }}</x-forms.error>
    @endif
</div>