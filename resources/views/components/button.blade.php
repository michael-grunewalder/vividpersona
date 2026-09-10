@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'neutral' => 'btn-neutral',
        'ghost' => 'btn-ghost',
        'outline' => 'btn-outline',
        'error' => 'btn-error',
    ];

    $sizes = [
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $classes = trim('btn '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? ''));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
