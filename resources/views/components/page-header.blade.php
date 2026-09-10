@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="space-y-1">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="text-base-content/60">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
