@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="vividpersona" class="scroll-smooth">
<head>
    <x-layouts.head :title="$title" :description="$description" />
    @stack('head')
</head>
<body class="min-h-screen bg-base-100 font-sans text-base-content antialiased">
    <x-navbar />

    <x-flash />

    <main>
        {{ $slot }}
    </main>

    <x-footer />

    @stack('scripts')
</body>
</html>
