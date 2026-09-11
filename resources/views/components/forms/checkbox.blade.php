@props([
    'name',
    'label' => null,
    'hint' => null,
    'checked' => false,
    'error' => null,
])

<div class="space-y-1.5">
    <label class="flex cursor-pointer items-start gap-3">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="1"
            class="checkbox checkbox-sm mt-0.5 {{ $error ? 'checkbox-error' : '' }}"
            @checked($checked)
            {{ $attributes }}
        />

        <span class="space-y-0.5">
            @if ($label)
                <span class="block text-sm font-medium">{{ $label }}</span>
            @endif

            @if ($hint)
                <span class="block text-xs text-base-content/50">{{ $hint }}</span>
            @endif
        </span>
    </label>

    @if ($error)
        <x-forms.error>{{ $error }}</x-forms.error>
    @endif
</div>