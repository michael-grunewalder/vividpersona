@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent' => 'badge-accent',
        'neutral' => 'badge-neutral',
        'ghost' => 'badge-ghost',
        'outline' => 'badge-outline',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
    ];

    $sizes = [
        'sm' => 'badge-sm',
        'md' => '',
        'lg' => 'badge-lg',
    ];

    $classes = trim('badge '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? ''));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
