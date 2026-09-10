<footer class="border-t border-base-300 bg-base-100">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-8 text-sm text-base-content/60 sm:flex-row sm:px-6 lg:px-8">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>

        <nav class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="link link-hover">Home</a>
            <a href="{{ route('dashboard') }}" class="link link-hover">Dashboard</a>
        </nav>
    </div>
</footer>
