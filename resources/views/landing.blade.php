@php
    $features = [
        [
            'title' => __('landing.feature_tailwind_title'),
            'description' => __('landing.feature_tailwind_desc'),
            'icon' => '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8M18.4 5.6L5.6 18.4"/></svg>',
        ],
        [
            'title' => __('landing.feature_daisyui_title'),
            'description' => __('landing.feature_daisyui_desc'),
            'icon' => '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
        ],
        [
            'title' => __('landing.feature_themes_title'),
            'description' => __('landing.feature_themes_desc'),
            'icon' => '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>',
        ],
        [
            'title' => __('landing.feature_alpine_title'),
            'description' => __('landing.feature_alpine_desc'),
            'icon' => '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>',
        ],
        [
            'title' => __('landing.feature_components_title'),
            'description' => __('landing.feature_components_desc'),
            'icon' => '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l8-4 8 4-8 4-8-4z"/><path d="M4 12l8 4 8-4M4 17l8 4 8-4"/></svg>',
        ],
        [
            'title' => __('landing.feature_laravel_title'),
            'description' => __('landing.feature_laravel_desc'),
            'icon' => '<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 5v10l-9 5-9-5V7l9-5z"/><path d="M12 12l9-5M12 12v10M12 12L3 7"/></svg>',
        ],
    ];

    $stats = [
        ['value' => 'v4', 'label' => __('landing.stat_tailwind')],
        ['value' => 'v5', 'label' => __('landing.stat_daisyui')],
        ['value' => 'v3', 'label' => __('landing.stat_alpine')],
        ['value' => '13', 'label' => __('landing.stat_laravel')],
    ];
@endphp

<x-layouts.app
    :title="__('landing.page_title')"
    :description="__('landing.page_description')"
>
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-40 -top-40 size-[42rem] rounded-full bg-primary/25 blur-3xl animate-orb"></div>
            <div class="absolute -right-32 -top-24 size-[34rem] rounded-full bg-secondary/20 blur-3xl animate-orb [animation-delay:-6s]"></div>
            <div class="absolute -bottom-72 left-1/3 size-[40rem] rounded-full bg-accent/15 blur-3xl animate-orb [animation-delay:-12s]"></div>
            <div
                class="absolute inset-0 text-base-content/10"
                style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 32px 32px;"
            ></div>
        </div>

        <div class="mx-auto flex max-w-4xl flex-col items-center px-4 py-24 text-center sm:px-6 sm:py-32 lg:px-8">
            <x-badge variant="primary" class="gap-2 px-3 py-2.5">
                <span class="relative flex size-2">
                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-primary"></span>
                </span>
                {{ __('landing.badge') }}
            </x-badge>

            <h1 class="mt-6 text-4xl font-extrabold tracking-tight sm:text-6xl">
                {{ __('landing.hero_title') }}
                <span class="bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">{{ __('landing.hero_highlight') }}</span>
            </h1>

            <p class="mt-6 max-w-2xl text-lg text-base-content/70">
                {{ __('landing.hero_subtitle') }}
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <x-button :href="route('dashboard')" size="lg">{{ __('landing.open_dashboard') }}</x-button>
                <x-button href="#features" variant="outline" size="lg">{{ __('landing.explore_features') }}</x-button>
            </div>

            <dl class="mt-16 grid w-full max-w-2xl grid-cols-2 gap-6 sm:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="rounded-box border border-base-300 bg-base-100/60 px-4 py-5 backdrop-blur">
                        <dt class="text-2xl font-bold text-primary">{{ $stat['value'] }}</dt>
                        <dd class="mt-1 text-sm text-base-content/60">{{ $stat['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section id="features" class="border-t border-base-300 bg-base-200/50 py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ __('landing.features_heading') }}</h2>
                <p class="mt-4 text-base-content/70">
                    {{ __('landing.features_subtitle') }}
                </p>
            </div>

            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $feature)
                    <x-card>
                        <span class="grid size-10 place-items-center rounded-lg bg-primary/10 text-primary">
                            {!! $feature['icon'] !!}
                        </span>
                        <h3 class="text-base font-semibold">{{ $feature['title'] }}</h3>
                        <p class="text-sm text-base-content/60">{{ $feature['description'] }}</p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    <section id="theming" class="py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ __('landing.theming_heading') }}</h2>
                <p class="mt-4 text-base-content/70">
                    {{ __('landing.theming_subtitle', ['file' => 'resources/css/app.css']) }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button :href="route('dashboard')" variant="primary">{{ __('landing.see_in_app') }}</x-button>
                    <x-button href="#features" variant="ghost">{{ __('landing.back_to_features') }}</x-button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ([
                    ['class' => 'bg-primary', 'label' => 'bg-primary'],
                    ['class' => 'bg-secondary', 'label' => 'bg-secondary'],
                    ['class' => 'bg-accent', 'label' => 'bg-accent'],
                    ['class' => 'bg-neutral', 'label' => 'bg-neutral'],
                    ['class' => 'bg-base-100', 'label' => 'bg-base-100'],
                    ['class' => 'bg-base-300', 'label' => 'bg-base-300'],
                ] as $swatch)
                    <div class="overflow-hidden rounded-box border border-base-300 bg-base-100 shadow-sm">
                        <div class="h-20 {{ $swatch['class'] }}"></div>
                        <p class="px-3 py-2 text-xs font-medium text-base-content/60">{{ $swatch['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-base-300 bg-base-200/50 py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-box border border-base-300 bg-base-100 px-8 py-14 text-center shadow-sm">
                <div class="pointer-events-none absolute -right-24 -top-24 size-72 rounded-full bg-secondary/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-24 size-72 rounded-full bg-primary/20 blur-3xl"></div>

                <div class="relative">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ __('landing.cta_heading') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-base-content/70">
                        {{ __('landing.cta_subtitle') }}
                    </p>
                    <div class="mt-8 flex justify-center">
                        <x-button :href="route('dashboard')" size="lg">{{ __('landing.open_dashboard') }}</x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
