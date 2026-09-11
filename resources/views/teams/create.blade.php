<x-layouts.dashboard :title="__('teams.create_team')">
    <x-page-header :title="__('teams.create_team')" :subtitle="__('teams.create_subtitle')" />

    <div class="mt-8 max-w-xl">
        <x-card>
            <form method="POST" action="{{ route('teams.store') }}" class="space-y-4">
                @csrf

                <x-forms.input
                    name="name"
                    :label="__('teams.name')"
                    :value="old('name')"
                    :error="$errors->first('name')"
                    required
                    autofocus
                />

                <div class="flex items-center gap-3">
                    <x-button type="submit">{{ __('teams.create_team') }}</x-button>
                    <x-button :href="route('teams.index')" variant="ghost">{{ __('teams.cancel') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.dashboard>