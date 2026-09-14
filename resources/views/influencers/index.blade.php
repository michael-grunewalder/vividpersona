<x-layouts.dashboard :title="__('influencers.title')">
    <x-page-header :title="__('influencers.title')" :subtitle="__('influencers.subtitle')">
        <x-slot:actions>
            <x-button :href="route('influencers.create')">{{ __('influencers.create') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($influencers->isEmpty())
        <x-empty-state
            :title="__('influencers.empty_title')"
            :description="__('influencers.empty_description')"
            class="mt-8"
        >
            <x-slot:action>
                <x-button :href="route('influencers.create')">{{ __('influencers.create') }}</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($influencers as $influencer)
                <a
                    href="{{ route('influencers.show', $influencer) }}"
                    class="card border border-base-300 bg-base-100 shadow-sm transition hover:border-primary/50"
                >
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            @if ($influencer->main_image)
                                <img src="{{ $influencer->main_image }}" alt="" class="size-14 rounded-xl object-cover" />
                            @else
                                <span class="grid size-14 place-items-center rounded-xl bg-base-200 text-lg font-bold">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($influencer->name, 0, 2)) }}
                                </span>
                            @endif

                            <div class="min-w-0">
                                <h3 class="card-title truncate text-base">{{ $influencer->name }}</h3>
                                <p class="text-sm text-base-content/60">{{ $influencer->gender }}, {{ $influencer->age }}</p>
                            </div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-badge :variant="$influencer->status === 'ready' ? 'success' : 'ghost'">
                                {{ __('influencers.status_'.$influencer->status) }}
                            </x-badge>
                            <x-badge variant="ghost">
                                {{ count($influencer->generation_history ?? []) }} {{ __('influencers.sets') }}
                            </x-badge>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>