@if (auth()->check())
    @php($user = auth()->user())

    <div
        x-data="{
            open: false,
            theme: document.documentElement.getAttribute('data-theme'),
            toggleTheme() {
                this.theme = this.theme === 'vividpersona-dark' ? 'vividpersona' : 'vividpersona-dark';
                document.documentElement.setAttribute('data-theme', this.theme);
                localStorage.setItem('theme', this.theme);
            },
        }"
        class="relative"
    >
        <button
            type="button"
            class="flex items-center gap-2 rounded-full p-1 pr-2 transition hover:bg-base-200"
            @click="open = !open"
            @click.away="open = false"
            :aria-expanded="open"
            aria-label="{{ __('user_menu.label') }}"
        >
            <span class="relative grid size-8 place-items-center overflow-hidden rounded-full bg-gradient-to-br from-primary to-secondary text-xs font-bold text-primary-content">
                {{ $user->initials() }}

                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="" class="absolute inset-0 size-full object-cover" onerror="this.remove()" />
                @endif
            </span>

            <span class="hidden text-sm font-semibold sm:inline">{{ $user->name }}</span>

            <svg class="size-3 text-base-content/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9l6 6 6-6" />
            </svg>
        </button>

        <div x-show="open" x-cloak x-transition @click.away="open = false" class="absolute right-0 z-50 mt-2 w-60 rounded-box border border-base-300 bg-base-100 p-1 shadow-lg">
            <div class="flex items-center gap-3 px-3 py-2">
                <span class="relative grid size-10 shrink-0 place-items-center overflow-hidden rounded-full bg-gradient-to-br from-primary to-secondary text-sm font-bold text-primary-content">
                    {{ $user->initials() }}

                    @if ($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" alt="" class="absolute inset-0 size-full object-cover" onerror="this.remove()" />
                    @endif
                </span>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                    <p class="truncate text-xs text-base-content/60">{{ $user->email }}</p>
                </div>
            </div>

            <div class="my-1 border-t border-base-300"></div>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-base-200">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8" />
                </svg>
                {{ __('user_menu.profile') }}
            </a>

            <button
                type="button"
                @click="toggleTheme()"
                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm hover:bg-base-200"
            >
                <svg class="size-4 block dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>
                <svg class="size-4 hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5" />
                    <line x1="12" y1="1" x2="12" y2="3" /><line x1="12" y1="21" x2="12" y2="23" />
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" /><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                    <line x1="1" y1="12" x2="3" y2="12" /><line x1="21" y1="12" x2="23" y2="12" />
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" /><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                </svg>
                <span class="dark:hidden">{{ __('user_menu.theme_dark') }}</span>
                <span class="hidden dark:block">{{ __('user_menu.theme_light') }}</span>
            </button>

            <a href="{{ route('teams.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-base-200">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                {{ __('user_menu.teams') }}
            </a>

            <div class="my-1 border-t border-base-300"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm text-error hover:bg-base-200">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>
                    {{ __('user_menu.logout') }}
                </button>
            </form>
        </div>
    </div>
@endif