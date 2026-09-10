@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow-sm']) }}>
    <div class="card-body gap-4">
        @if ($title || isset($actions))
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    @if ($title)
                        <h3 class="card-title text-base">{{ $title }}</h3>
                    @endif

                    @if ($subtitle)
                        <p class="text-sm text-base-content/60">{{ $subtitle }}</p>
                    @endif
                </div>

                @isset($actions)
                    <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
                @endisset
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
