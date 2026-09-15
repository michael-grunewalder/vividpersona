@php
    $modelOptions = $providers
        ->mapWithKeys(fn ($item) => [$item['service']->value => $item['models']])
        ->all();
@endphp

<x-layouts.dashboard :title="$persona->name">
    <x-page-header :title="$persona->name" :subtitle="$persona->physical_desc ?? ''">
        <x-slot:actions>
            <x-button :href="route('personas.index')" variant="ghost">{{ __('personas.back') }}</x-button>
            <form
                method="POST"
                action="{{ route('personas.destroy', $persona) }}"
                onsubmit="return confirm('{{ __('personas.delete_confirm') }}')"
            >
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="error" size="sm">{{ __('personas.delete') }}</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div
        x-data="{
            status: {{ Illuminate\Support\Js::from($persona->status) }},
            sets: {{ Illuminate\Support\Js::from($persona->generation_history ?? []) }},
            mainImage: {{ Illuminate\Support\Js::from($persona->main_image) }},
            lastError: {{ Illuminate\Support\Js::from($persona->last_error) }},
            stalled: false,
            pollTimer: null,
            stallTimer: null,
            init() {
                if (['pending', 'generating'].includes(this.status)) {
                    this.pollTimer = setInterval(() => this.refresh(), 4000);
                    this.stallTimer = setTimeout(() => {
                        clearInterval(this.pollTimer);
                        this.stalled = true;
                    }, 300000);
                }
            },
            async refresh() {
                const d = await (await fetch('{{ route('personas.status', $persona) }}')).json();
                this.status = d.status;
                this.sets = d.sets ?? [];
                this.lastError = d.last_error ?? null;
                if (['ready', 'failed'].includes(this.status)) {
                    clearInterval(this.pollTimer);
                    clearTimeout(this.stallTimer);
                }
            },
        }"
    >
        <div class="mt-6 flex items-center gap-2">
            <span x-show="status === 'pending'" class="badge badge-ghost">{{ __('personas.status_pending') }}</span>
            <span x-show="status === 'generating'" class="badge badge-primary">
                <span class="loading loading-spinner loading-xs mr-1"></span>{{ __('personas.status_generating') }}
            </span>
            <span x-show="status === 'ready'" class="badge badge-success">{{ __('personas.status_ready') }}</span>
            <span x-show="status === 'failed'" class="badge badge-error">{{ __('personas.status_failed') }}</span>
        </div>

        <div x-show="status === 'failed'" class="mt-8 rounded-box border border-error bg-error/10 p-6">
            <p class="font-semibold text-error">{{ __('personas.generation_failed') }}</p>
            <p class="mt-1 text-sm text-base-content/70" x-text="lastError"></p>
        </div>

        <div x-show="stalled" class="mt-8 rounded-box border border-warning bg-warning/10 p-6">
            <p class="font-semibold">{{ __('personas.stalled') }}</p>
            <p class="mt-1 text-sm text-base-content/70">{{ __('personas.stalled_hint') }}</p>
            <div class="mt-3">
                <a href="{{ request()->url() }}" class="btn btn-outline btn-sm">{{ __('personas.refresh') }}</a>
            </div>
        </div>

        <div x-show="(status === 'pending' || status === 'generating') && ! stalled" class="mt-8 flex items-center gap-3 rounded-box border border-base-300 bg-base-100 p-6">
            <span class="loading loading-spinner loading-md text-primary"></span>
            <div>
                <p class="font-semibold">{{ __('personas.generating') }}</p>
                <p class="text-sm text-base-content/60">{{ __('personas.generating_hint') }}</p>
            </div>
        </div>

        @if ($persona->main_image)
            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <img src="{{ $persona->imageUrl() }}" alt="" class="w-full rounded-box border border-base-300 shadow-sm lg:col-span-1" />
                <div class="lg:col-span-2">
                    <x-card :title="__('personas.details')">
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-base-content/60">{{ __('personas.gender') }}</dt><dd>{{ $persona->gender }}</dd>
                            <dt class="text-base-content/60">{{ __('personas.age') }}</dt><dd>{{ $persona->age }}</dd>
                            <dt class="text-base-content/60">{{ __('personas.niches') }}</dt><dd>{{ implode(', ', $persona->niches ?? []) }}</dd>
                            <dt class="text-base-content/60">{{ __('personas.vibe') }}</dt><dd>{{ implode(', ', $persona->vibe_words ?? []) }}</dd>
                            <dt class="text-base-content/60">{{ __('personas.backstory') }}</dt><dd>{{ $persona->backstory }}</dd>
                        </dl>
                    </x-card>
                </div>
            </div>
        @endif

        @if ($persona->referenceImageUrl())
            <x-card :title="__('personas.reference_image')" :subtitle="__('personas.reference_hint')" class="mt-8">
                <div class="flex items-center gap-2" x-data="{ copied: false }">
                    <input type="text" value="{{ $persona->referenceImageUrl() }}" readonly class="input input-bordered w-full font-mono text-xs" />
                    <button
                        type="button"
                        class="btn btn-outline btn-sm shrink-0"
                        @click="navigator.clipboard.writeText('{{ $persona->referenceImageUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <span x-show="!copied">{{ __('personas.copy_link') }}</span>
                        <span x-show="copied">{{ __('personas.copied') }}</span>
                    </button>
                </div>
            </x-card>
        @endif

        <template x-for="(set, i) in sets" :key="set.id">
            <div class="card mt-8 border border-base-300 bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="card-title text-base">
                                {{ __('personas.set_label') }} <span x-text="i + 1"></span>
                            </h3>
                            <p class="mt-1 text-xs text-base-content/60" x-text="(set.provider || '') + ' · ' + (set.model || '') + ' · ' + (set.aspect_ratio || '')"></p>
                        </div>

                        <span x-show="set.status === 'generating'" class="badge badge-primary">
                            <span class="loading loading-spinner loading-xs mr-1"></span>{{ __('personas.status_generating') }}
                        </span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <template x-for="image in set.images" :key="image.id">
                            <div class="space-y-2">
                                <template x-if="image.url">
                                    <img :src="image.url" alt="" class="w-full rounded-box border border-base-300" />
                                </template>

                                <template x-if="!image.url">
                                    <div class="grid aspect-[3/4] w-full place-items-center rounded-box border border-dashed border-base-300 px-4 text-center text-sm text-base-content/50">
                                        {{ __('personas.generation_failed') }}
                                    </div>
                                </template>

                                <template x-if="image.url && mainImage !== image.url">
                                    <form method="POST" action="{{ route('personas.choose', $persona) }}">
                                        @csrf
                                        <input type="hidden" name="image_id" :value="image.id" />
                                        <button type="submit" class="btn btn-sm btn-outline w-full">{{ __('personas.use_this') }}</button>
                                    </form>
                                </template>

                                <template x-if="image.url && mainImage === image.url">
                                    <span class="badge badge-success w-full justify-center">{{ __('personas.chosen') }}</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        @if ($providers->isNotEmpty() && $persona->reference_image_path === null)
            <x-card :title="__('personas.generate_more')" :subtitle="__('personas.generate_more_hint')" class="mt-8">
                <form method="POST" action="{{ route('personas.generate-set', $persona) }}" class="grid max-w-2xl gap-4 sm:grid-cols-3">
                    @csrf

                    <x-forms.select
                        name="provider"
                        :label="__('personas.provider')"
                        :options="$providers->mapWithKeys(fn ($item) => [$item['service']->value => $item['service']->label()])->all()"
                        :selected="old('provider', $persona->provider)"
                    />

                    <x-forms.select
                        name="model"
                        :label="__('personas.model')"
                        :options="collect($modelOptions[(string) $persona->provider] ?? [])->mapWithKeys(fn ($label, $key) => [$key => $label])->all()"
                        :selected="old('model', $persona->model)"
                    />

                    <x-forms.select
                        name="aspect_ratio"
                        :label="__('personas.aspect_ratio')"
                        :options="['9:16' => '9:16', '16:9' => '16:9']"
                        :selected="old('aspect_ratio', $persona->aspect_ratio)"
                    />

                    @if ($llmProviders->isNotEmpty())
                        <div class="sm:col-span-3">
                            <x-forms.select
                                name="llm_provider"
                                :label="__('personas.ai_provider')"
                                :options="collect(['' => __('personas.ai_provider_default')])->union($llmProviders->mapWithKeys(fn ($service) => [$service->value => $service->label()]))->all()"
                                :selected="old('llm_provider', $persona->llm_provider)"
                            />
                        </div>
                    @endif

                    <div class="sm:col-span-3">
                        <x-button type="submit">{{ __('personas.generate_more') }}</x-button>
                    </div>
                </form>
            </x-card>
        @endif
    </div>
</x-layouts.dashboard>