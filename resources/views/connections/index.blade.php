<x-layouts.dashboard :title="__('connections.title')">
    <x-page-header :title="__('connections.title')" :subtitle="__('connections.subtitle')" />

    <div class="mt-8 space-y-8">
        <section>
            <h2 class="text-lg font-semibold">{{ __('connections.media_title') }}</h2>
            <p class="mt-1 text-sm text-base-content/60">{{ __('connections.media_subtitle') }}</p>

            <div class="mt-4 grid gap-6 md:grid-cols-2">
                @foreach ($media as $item)
                    @include('connections._card', ['item' => $item])
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="text-lg font-semibold">{{ __('connections.ai_title') }}</h2>
            <p class="mt-1 text-sm text-base-content/60">{{ __('connections.ai_subtitle') }}</p>

            <div class="mt-4 grid gap-6 md:grid-cols-2">
                @foreach ($llm as $item)
                    @include('connections._card', ['item' => $item])
                @endforeach
            </div>

            <x-card class="mt-6">
                <form method="POST" action="{{ route('connections.default-llm') }}" class="grid max-w-xl gap-4 sm:grid-cols-2">
                    @csrf

                    <x-forms.select
                        name="default_llm"
                        :label="__('connections.default_llm')"
                        :options="collect(['' => __('connections.default_llm_none')])->union($llm->pluck('service')->mapWithKeys(fn ($s) => [$s->value => $s->label()]))->all()"
                        :selected="old('default_llm', $defaultLlm?->value)"
                    />

                    <div class="flex items-end">
                        <x-button type="submit">{{ __('connections.save') }}</x-button>
                    </div>
                </form>
            </x-card>
        </section>
    </div>
</x-layouts.dashboard>