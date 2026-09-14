@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'error' => null,
    'placeholder' => null,
])

<div class="space-y-1.5">
    @if ($label)
        <x-forms.label for="{{ $name }}">{{ $label }}</x-forms.label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge(['class' => 'select select-bordered w-full '.($error ? 'select-error' : '')]) }}
    >
        @if ($placeholder)
            <option value="" disabled>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected($selected == $value)>{{ $optionLabel }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if ($error)
        <x-forms.error>{{ $error }}</x-forms.error>
    @endif
</div>