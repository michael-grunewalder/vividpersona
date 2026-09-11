<x-layouts.dashboard :title="__('dashboard.overview')">
    <x-page-header :title="__('dashboard.overview')" :subtitle="__('dashboard.overview_subtitle')">
        <x-slot:actions>
            <x-button size="sm" variant="outline">{{ __('dashboard.secondary_action') }}</x-button>
            <x-button size="sm">{{ __('dashboard.primary_action') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <x-card :title="__('dashboard.getting_started_title')" :subtitle="__('dashboard.getting_started_subtitle')">
            <p class="text-sm text-base-content/60">
                {{ __('dashboard.getting_started_body') }}
            </p>
            <x-slot:actions>
                <x-badge variant="primary">New</x-badge>
            </x-slot:actions>
        </x-card>

        <x-card :title="__('dashboard.components_title')" :subtitle="__('dashboard.components_subtitle')">
            <p class="text-sm text-base-content/60">
                {{ __('dashboard.components_body', ['path' => 'resources/views/components']) }}
            </p>
        </x-card>

        <x-card :title="__('dashboard.themed_title')" :subtitle="__('dashboard.themed_subtitle')">
            <p class="text-sm text-base-content/60">
                {{ __('dashboard.themed_body') }}
            </p>
        </x-card>
    </div>

    <div class="mt-6">
        <x-empty-state :title="__('dashboard.empty_title')" :description="__('dashboard.empty_description')">
            <x-slot:icon>
                <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                </svg>
            </x-slot:icon>
            <x-slot:action>
                <x-button size="sm" variant="outline">{{ __('dashboard.create_something') }}</x-button>
            </x-slot:action>
        </x-empty-state>
    </div>
</x-layouts.dashboard>