<x-layouts.dashboard :title="__('admin.users.title')">
    <x-page-header :title="__('admin.users.title')" :subtitle="__('admin.users.subtitle')">
        <x-slot:actions>
            <x-button :href="route('backend.user.create')">{{ __('admin.users.add') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('backend.user.index') }}" class="mt-8 flex max-w-md gap-2">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            class="input input-bordered w-full"
            :placeholder="'{{ __('admin.users.search') }}'"
        />
        <x-button type="submit" variant="outline">{{ __('admin.users.search') }}</x-button>
    </form>

    @if ($users->isEmpty())
        <x-empty-state
            :title="__('admin.users.empty_title')"
            :description="__('admin.users.empty_description')"
            class="mt-4"
        />
    @else
        <div class="mt-4 overflow-x-auto rounded-box border border-base-300 bg-base-100 shadow-sm">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('admin.users.name') }}</th>
                        <th>{{ __('admin.users.email') }}</th>
                        <th>{{ __('admin.users.plan') }}</th>
                        <th>{{ __('admin.users.max_teams') }}</th>
                        <th>{{ __('admin.users.status') }}</th>
                        <th class="text-right">{{ __('admin.users.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="font-medium">
                                {{ $user->name }}
                                @if ($user->is_super_admin)
                                    <x-badge variant="secondary" class="ml-1">{{ __('admin.users.super_admin') }}</x-badge>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <x-badge variant="primary">{{ $user->meta->currentPlan->label() }}</x-badge>
                            </td>
                            <td>{{ $user->meta->maxTeams }}</td>
                            <td>
                                @if ($user->hasVerifiedEmail())
                                    <x-badge variant="success">{{ __('admin.users.verified') }}</x-badge>
                                @else
                                    <x-badge variant="ghost">{{ __('admin.users.unverified') }}</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <x-button :href="route('backend.user.edit', $user)" size="xs" variant="outline">
                                        {{ __('admin.users.edit') }}
                                    </x-button>

                                    @if ($user->getKey() !== auth()->id())
                                        <form
                                            method="POST"
                                            action="{{ route('backend.user.destroy', $user) }}"
                                            onsubmit="return confirm('{{ __('admin.users.delete_confirm') }}')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="xs" variant="error">{{ __('admin.users.delete') }}</x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</x-layouts.dashboard>