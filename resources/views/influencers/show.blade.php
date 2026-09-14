@php
    $modelOptions = $providers
        ->mapWithKeys(fn ($item) => [$item['provider']->machine_name => $item['models']])
        ->all();
@endphp

<x-layouts.dashboard :title="$influencer->name">
    <x-page-header :title="$influencer->name" :subtitle="$influencer->physical_desc ?? ''">
        <x-slot:actions>
            <x-button :href="route('influencers.index')" variant="ghost">{{ __('influencers.back') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    <div
        x-data="{
            generating: {{ in_array($influencer->status, ['pending', 'generating'], true) ? 'true' : 'false' }},
            poll() {
                fetch('{{ route('influencers.status', $influencer) }}')
                    .then(r => r.json())
                    .then(d => { if (d.status === 'ready') window.location.reload(); });
            },
        }"
        x-init="if (generating) { const t = setInterval(() => { if (! generating) { clearInterval(t); return; } poll(); }, 3000); }"
    >
        @if ($influencer->main_image)
            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <img src="{{ $influencer->main_image }}" alt="" class="w-full rounded-box border border-base-300 shadow-sm lg:col-span-1" />
                <div class="lg:col-span-2">
                    <x-card :title="__('influencers.details')">
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-base-content/60">{{ __('influencers.gender') }}</dt><dd>{{ $influencer->gender }}</dd>
                            <dt class="text-base-content/60">{{ __('influencers.age') }}</dt><dd>{{ $influencer->age }}</dd>
                            <dt class="text-base-content/60">{{ __('influencers.niches') }}</dt><dd>{{ implode(', ', $influencer->niches ?? []) }}</dd>
                            <dt class="text-base-content/60">{{ __('influencers.vibe') }}</dt><dd>{{ implode(', ', $influencer->vibe_words ?? []) }}</dd>
                            <dt class="text-base-content/60">{{ __('influencers.backstory') }}</dt><dd>{{ $influencer->backstory }}</dd>
                        </dl>
                    </x-card>
                </div>
            </div>
        @endif

        @if (in_array($influencer->status, ['pending', 'generating'], true))
            <div class="mt-8 flex items-center gap-3 rounded-box border border-base-300 bg-base-100 p-6">
                <span class="loading loading-spinner loading-md text-primary"></span>
                <div>
                    <p class="font-semibold">{{ __('influencers.generating') }}</p>
                    <p class="text-sm text-base-content/60">{{ __('influencers.generating_hint') }}</p>
                </div>
            </div>
        @endif

        @foreach (($influencer->generation_history ?? []) as $set)
            <x-card :title="__('influencers.set_of', ['n' => $loop->iteration])" class="mt-8">
                <p class="mb-4 text-xs text-base-content/60">
                    {{ $set['provider'] ?? '' }} · {{ $set['model'] ?? '' }} · {{ $set['aspect_ratio'] ?? '' }}
                </p>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($set['images'] ?? [] as $image)
                        <div class="space-y-2">
                            @if (! empty($image['url']))
                                <img src="{{ $image['url'] }}" alt="" class="w-full rounded-box border border-base-300" />
                            @else
                                <div class="grid aspect-[3/4] w-full place-items-center rounded-box border border-dashed border-base-300 px-4 text-center text-sm text-base-content/50">
                                    {{ __('influencers.generation_failed') }}
                                </div>
                            @endif

                            @if (! empty($image['url']) && $influencer->main_image !== $image['url'])
                                <form method="POST" action="{{ route('influencers.choose', $influencer) }}">
                                    @csrf
                                    <input type="hidden" name="image_id" value="{{ $image['id'] }}" />
                                    <x-button type="submit" size="sm" variant="outline" class="w-full">{{ __('influencers.use_this') }}</x-button>
                                </form>
                            @elseif (! empty($image['url']))
                                <x-badge variant="success" class="w-full justify-center">{{ __('influencers.chosen') }}</x-badge>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endforeach

        @if ($providers->isNotEmpty())
            <x-card :title="__('influencers.generate_more')" :subtitle="__('influencers.generate_more_hint')" class="mt-8">
                <form method="POST" action="{{ route('influencers.generate-set', $influencer) }}" class="grid max-w-2xl gap-4 sm:grid-cols-3">
                    @csrf

                    <x-forms.select
                        name="provider"
                        :label="__('influencers.provider')"
                        :options="$providers->mapWithKeys(fn ($item) => [$item['provider']->machine_name => $item['provider']->friendly_name])->all()"
                        :selected="old('provider', $influencer->provider)"
                    />

                    <x-forms.select
                        name="model"
                        :label="__('influencers.model')"
                        :options="collect($modelOptions[$influencer->provider] ?? [])->mapWithKeys(fn ($label, $key) => [$key => $label])->all()"
                        :selected="old('model', $influencer->model)"
                    />

                    <x-forms.select
                        name="aspect_ratio"
                        :label="__('influencers.aspect_ratio')"
                        :options="['9:16' => '9:16', '16:9' => '16:9']"
                        :selected="old('aspect_ratio', $influencer->aspect_ratio)"
                    />

                    <div class="sm:col-span-3">
                        <x-button type="submit">{{ __('influencers.generate_more') }}</x-button>
                    </div>
                </form>
            </x-card>
        @endif
    </div>
</x-layouts.dashboard>