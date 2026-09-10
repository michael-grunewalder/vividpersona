@props([
    'title' => null,
    'description' => null,
])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif

{{-- Apply the saved/system theme before first paint to avoid a flash. --}}
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = stored || (prefersDark ? 'vividpersona-dark' : 'vividpersona');
            document.documentElement.setAttribute('data-theme', theme);
        } catch (e) {}
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
