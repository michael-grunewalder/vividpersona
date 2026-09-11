<footer class="border-t border-base-300 bg-base-100">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-8 text-sm text-base-content/60 sm:flex-row sm:px-6 lg:px-8">
        <p>{{ __('footer.all_rights_reserved', ['year' => date('Y'), 'app' => config('app.name')]) }}</p>

        <nav class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="link link-hover">{{ __('footer.home') }}</a>

            @auth
                <a href="{{ route('dashboard') }}" class="link link-hover">{{ __('footer.dashboard') }}</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link link-hover">{{ __('auth.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="link link-hover">{{ __('auth.login') }}</a>
                <a href="{{ route('register') }}" class="link link-hover">{{ __('auth.register') }}</a>
            @endauth
        </nav>
    </div>
</footer>