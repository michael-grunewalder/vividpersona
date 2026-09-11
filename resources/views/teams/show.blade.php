<x-layouts.dashboard :title="$team->name">
    <x-page-header :title="$team->name" :subtitle="__('teams.show_subtitle')">
        <x-slot:actions>
            <x-button :href="route('teams.index')" variant="ghost">{{ __('teams.back') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @can('update', $team)
        <x-card :title="__('teams.rename')" class="mt-8">
            <form method="POST" action="{{ route('teams.update', $team) }}" class="flex max-w-lg items-end gap-3">
                @csrf
                @method('PATCH')

                <div class="flex-1">
                    <x-forms.input
                        name="name"
                        :label="__('teams.name')"
                        :value="old('name', $team->name)"
                        :error="$errors->first('name')"
                        required
                    />
                </div>

                <x-button type="submit">{{ __('teams.save') }}</x-button>
            </form>
        </x-card>
    @endcan

    <x-card :title="__('teams.members')" class="mt-8">
        @if ($members->isEmpty())
            <p class="text-sm text-base-content/60">{{ __('teams.no_members') }}</p>
        @else
            <ul class="divide-y divide-base-300">
                @foreach ($members as $member)
                    <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div>
                            <p class="font-medium">
                                {{ $member['user']->name }}
                                @if ($member['user']->getKey() === $team->owner_id)
                                    <x-badge variant="secondary" class="ml-1">{{ __('roles.owner') }}</x-badge>
                                @endif
                            </p>
                            <p class="text-sm text-base-content/60">{{ $member['user']->email }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            @can('assignRoles', $team)
                                @if ($member['user']->getKey() !== $team->owner_id)
                                    <form method="POST" action="{{ route('teams.members.update', [$team, $member['user']]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="role"
                                            class="select select-bordered select-sm"
                                            onchange="this.form.submit()"
                                        >
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->value }}" @selected($member['role'] === $role->value)>
                                                    {{ $role->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            @else
                                <x-badge variant="primary">{{ __('roles.'.$member['role']) }}</x-badge>
                            @endcan

                            @can('manageMembers', $team)
                                @if ($member['user']->getKey() !== $team->owner_id)
                                    <form
                                        method="POST"
                                        action="{{ route('teams.members.destroy', [$team, $member['user']]) }}"
                                        onsubmit="return confirm('{{ __('teams.remove_member_confirm') }}')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-error" aria-label="{{ __('teams.member_removed') }}">✕</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    @can('manageMembers', $team)
        <x-card :title="__('invitations.invite_title')" :subtitle="__('invitations.invite_subtitle')" class="mt-8">
            <form method="POST" action="{{ route('teams.invitations.store', $team) }}" class="max-w-lg space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-forms.input
                        name="name"
                        :label="__('invitations.name')"
                        :value="old('name')"
                        :error="$errors->first('name')"
                        required
                    />

                    <x-forms.input
                        name="email"
                        type="email"
                        :label="__('invitations.email')"
                        :value="old('email')"
                        :error="$errors->first('email')"
                        required
                    />
                </div>

                <x-forms.select
                    name="role"
                    :label="__('invitations.role')"
                    :options="collect($roles)->mapWithKeys(fn ($role) => [$role->value => $role->label()])->all()"
                    :selected="old('role', 'viewer')"
                    :error="$errors->first('role')"
                    required
                />

                <x-button type="submit">{{ __('invitations.send') }}</x-button>
            </form>
        </x-card>
    @endcan
</x-layouts.dashboard>