@php
    $links = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Features', 'url' => route('home').'#features', 'active' => false],
        ['label' => 'Theming', 'url' => route('home').'#theming', 'active' => false],
    ];
@endphp

<header
    x-data="{ open: false, scrolled: false }"
    @scroll.window="scrolled = window.scrollY > 8"
    class="sticky top-0 z-50 border-b border-base-300/60 transition-colors duration-300"
    :class="scrolled ? 'bg-base-100/85 backdrop-blur-xl' : 'bg-base-100/60 backdrop-blur-md'"
>
    <div class="mx-auto flex h-16 w-full max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold tracking-tight">
            <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-br from-primary to-secondary text-primary-content shadow-sm">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8" />
                </svg>
            </span>
            <span class="text-[15px]">{{ config('app.name') }}</span>
        </a>

        <nav class="ml-6 hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                <a
                    href="{{ $link['url'] }}"
                    class="btn btn-ghost btn-sm {{ $link['active'] ? 'text-primary' : 'text-base-content/70' }}"
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1.5">
            <x-theme-toggle />

            <x-button :href="route('dashboard')" size="sm" class="hidden sm:inline-flex">
                Open dashboard
            </x-button>

            <button
                type="button"
                class="btn btn-square btn-ghost btn-sm md:hidden"
                @click="open = !open"
                :aria-expanded="open"
                aria-label="Toggle navigation"
            >
                <svg x-show="!open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak x-transition.opacity @click.away="open = false" class="border-t border-base-300 bg-base-100 md:hidden">
        <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 sm:px-6">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="btn btn-ghost btn-sm justify-start {{ $link['active'] ? 'text-primary' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <x-button :href="route('dashboard')" size="sm" class="mt-1">Open dashboard</x-button>
        </nav>
    </div>
</header>
