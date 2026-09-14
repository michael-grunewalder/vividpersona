@if (auth()->check())
    @php
        $user = auth()->user();
        $teams = $user->teams;
        $currentTeam = $user->currentTeam();
    @endphp

    <div x-data="{ open: false }" {{ $attributes->merge(['class' => 'relative']) }}>
        <button
            type="button"
            class="flex w-full items-center justify-between gap-2 rounded-box border border-base-300 bg-base-100 px-3 py-2 text-sm font-semibold transition hover:bg-base-200"
            @click="open = !open"
            @click.away="open = false"
            :aria-expanded="open"
            :aria-label="'{{ __('teams.switch_team') }}'"
        >
            <span class="flex min-w-0 items-center gap-2">
                <svg class="size-4 shrink-0 text-base-content/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span class="truncate">{{ $currentTeam?->name ?? __('teams.select_team') }}</span>
            </span>

            <svg class="size-3 shrink-0 text-base-content/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9l6 6 6-6" />
            </svg>
        </button>

        <div x-show="open" x-cloak x-transition @click.away="open = false" class="absolute left-0 right-0 z-50 mt-2 rounded-box border border-base-300 bg-base-100 p-1 shadow-lg">
            @forelse ($teams as $team)
                <form method="POST" action="{{ route('current-team.update') }}">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->getKey() }}" />

                    <button
                        type="submit"
                        class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-base-200 {{ $team->getKey() === $currentTeam?->getKey() ? 'font-semibold' : '' }}"
                    >
                        <span class="truncate">{{ $team->name }}</span>

                        @if ($team->getKey() === $currentTeam?->getKey())
                            <svg class="size-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5" />
                            </svg>
                        @endif
                    </button>
                </form>
            @empty
                <p class="px-3 py-2 text-sm text-base-content/60">{{ __('teams.empty_title') }}</p>
            @endforelse
        </div>
    </div>
@endif