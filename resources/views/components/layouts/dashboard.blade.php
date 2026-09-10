@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="vividpersona">
<head>
    <x-layouts.head :title="$title" />
    @stack('head')
</head>
<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
    <div class="drawer lg:drawer-open">
        <input id="app-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex min-h-screen flex-col">
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-base-300 bg-base-100/90 px-4 backdrop-blur-xl lg:px-8">
                <label for="app-drawer" class="btn btn-square btn-ghost btn-sm lg:hidden" aria-label="Toggle navigation">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>

                <span class="font-semibold tracking-tight">{{ $title ?? 'Dashboard' }}</span>

                <div class="ml-auto flex items-center gap-1.5">
                    <x-theme-toggle />
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-8">
                {{ $slot }}
            </main>
        </div>

        <div class="drawer-side z-40">
            <label for="app-drawer" class="drawer-overlay" aria-label="Close navigation"></label>
            <x-sidebar />
        </div>
    </div>

    @stack('scripts')
</body>
</html>
