@props(['message' => null])

<p {{ $attributes->merge(['class' => 'mt-1 text-sm text-error']) }}>
    {{ $message ?? $slot }}
</p>