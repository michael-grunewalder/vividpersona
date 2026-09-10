@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-box border border-dashed border-base-300 bg-base-100/50 px-6 py-16 text-center']) }}>
    @isset($icon)
        <div class="mb-4 text-base-content/40">{{ $icon }}</div>
    @endisset

    <h3 class="text-lg font-semibold">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-base-content/60">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-6">{{ $action }}</div>
    @endisset
</div>
