<x-layouts.dashboard :title="$team->name">
    @php
        $humanBytes = static function (int $bytes): string {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $value = (float) $bytes;
            $i = 0;
            while ($value >= 1024 && $i < count($units) - 1) {
                $value /= 1024;
                $i++;
            }

            return round($value, 1).' '.$units[$i];
        };
    @endphp

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

        <x-card :title="__('teams.provider')" :subtitle="__('teams.provider_subtitle')" class="mt-8">
            <form method="POST" action="{{ route('teams.provider.update', $team) }}" class="flex max-w-lg items-end gap-3">
                @csrf
                @method('PATCH')

                <div class="flex-1">
                    <x-forms.select
                        name="default_media_provider"
                        :label="__('teams.default_provider')"
                        :options="collect(['' => __('teams.default_provider_none')])->union($mediaServices->mapWithKeys(fn ($service) => [$service->value => $service->label()]))->all()"
                        :selected="old('default_media_provider', $team->default_media_provider)"
                        :error="$errors->first('default_media_provider')"
                    />
                </div>

                <x-button type="submit">{{ __('teams.save') }}</x-button>
            </form>
        </x-card>

        <x-card :title="__('teams.storage')" :subtitle="__('teams.storage_subtitle')" class="mt-8">
            <p class="text-sm text-base-content/80">
                @if ($team->hasStorageQuota())
                    {{ __('teams.storage_usage', ['used' => $humanBytes($team->storageUsedBytes()), 'limit' => $humanBytes($team->storageLimitBytes())]) }}
                @else
                    {{ __('teams.storage_usage_unlimited', ['used' => $humanBytes($team->storageUsedBytes())]) }}
                @endif
            </p>

            <div class="mt-3 h-2 w-full max-w-lg overflow-hidden rounded-full bg-base-200">
                <div
                    class="h-full bg-primary"
                    style="width: {{ $team->hasStorageQuota() ? min(100, round($team->storageUsedBytes() / max(1, $team->storageLimitBytes()) * 100)) : 0 }}%"
                ></div>
            </div>

            <form method="POST" action="{{ route('teams.storage.update', $team) }}" class="mt-4 flex max-w-lg items-end gap-3">
                @csrf
                @method('PATCH')

                <div class="flex-1">
                    <x-forms.input
                        name="storage_limit_gb"
                        type="number"
                        min="0"
                        step="1"
                        :label="__('teams.storage_limit_gb')"
                        :value="old('storage_limit_gb', $team->storageLimitBytes() !== null ? round($team->storageLimitBytes() / 1024 / 1024 / 1024, 2) : '')"
                        :hint="__('teams.storage_limit_hint')"
                        :error="$errors->first('storage_limit_gb')"
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