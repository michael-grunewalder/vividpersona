@props(['value' => null])

<label {{ $attributes->merge(['class' => 'text-sm font-medium']) }}>
    {{ $value ?? $slot }}
</label>