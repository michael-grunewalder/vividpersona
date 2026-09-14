@php
    $navigation = [
        ['label' => __('nav.overview'), 'url' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
    ];
@endphp

<aside class="flex min-h-full w-72 flex-col border-r border-base-300 bg-base-100 p-4">
    <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-2 py-1.5 font-bold tracking-tight">
        <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-br from-primary to-secondary text-primary-content shadow-sm">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8" />
            </svg>
        </span>
        <span class="text-[15px]">{{ config('app.name') }}</span>
    </a>

    <x-team-switcher class="mt-6" />

    <ul class="menu mt-6 w-full gap-1 p-0">
        @foreach ($navigation as $item)
            <li>
                <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'active' : '' }}">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach

        @if (auth()->user()?->is_super_admin)
            <li class="menu-title mt-4">{{ __('admin.nav_title') }}</li>
            <li>
                <a href="{{ route('backend.user.index') }}" class="{{ request()->routeIs('backend.user.*') ? 'active' : '' }}">
                    {{ __('admin.nav.users') }}
                </a>
            </li>
            <li>
                <a href="{{ route('backend.api-provider.index') }}" class="{{ request()->routeIs('backend.api-provider.*') ? 'active' : '' }}">
                    {{ __('admin.nav.providers') }}
                </a>
            </li>
        @endif
    </ul>

    <div class="mt-auto flex items-center justify-between gap-2 rounded-box border border-base-300 p-2">
        <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">{{ __('nav.back_to_site') }}</a>
        <x-theme-toggle />
    </div>
</aside>
