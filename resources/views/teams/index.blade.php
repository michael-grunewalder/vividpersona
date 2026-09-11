<x-layouts.dashboard :title="__('teams.title')">
    <x-page-header :title="__('teams.title')" :subtitle="__('teams.subtitle')">
        <x-slot:actions>
            <x-button :href="route('teams.create')">{{ __('teams.create_team') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($invitations->isNotEmpty())
        <x-card :title="__('invitations.title')" class="mt-8">
            @foreach ($invitations as $invitation)
                <div class="flex items-center justify-between gap-4 border-b border-base-300 py-3 first:pt-0 last:border-0 last:pb-0">
                    <div>
                        <p class="font-medium">{{ $invitation->team->name }}</p>
                        <p class="text-sm text-base-content/60">{{ $invitation->role->label() }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <form method="POST" action="{{ route('teams.invitations.accept', $invitation) }}">
                            @csrf
                            <x-button type="submit" size="sm">{{ __('invitations.accept') }}</x-button>
                        </form>
                        <form method="POST" action="{{ route('teams.invitations.decline', $invitation) }}">
                            @csrf
                            <x-button type="submit" size="sm" variant="ghost">{{ __('invitations.decline') }}</x-button>
                        </form>
                    </div>
                </div>
            @endforeach
        </x-card>
    @endif

    @if ($teams->isEmpty())
        <x-empty-state
            :title="__('teams.empty_title')"
            :description="__('teams.empty_description')"
            class="mt-8"
        >
            <x-slot:action>
                <x-button :href="route('teams.create')">{{ __('teams.create_team') }}</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($teams as $item)
                @php($team = $item['team'])
                <a
                    href="{{ route('teams.show', $team) }}"
                    class="card border border-base-300 bg-base-100 shadow-sm transition hover:border-primary/50"
                >
                    <div class="card-body">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="card-title text-base">{{ $team->name }}</h3>
                            <x-badge variant="primary">{{ __('roles.'.$item['role']) }}</x-badge>
                        </div>
                        <p class="text-sm text-base-content/60">{{ __('teams.member_count', ['count' => $team->members_count]) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>