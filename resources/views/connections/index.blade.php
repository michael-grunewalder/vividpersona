<x-layouts.dashboard :title="__('connections.title')">
    <x-page-header :title="__('connections.title')" :subtitle="__('connections.subtitle')" />

    @if ($providers->isEmpty())
        <x-empty-state
            :title="__('connections.empty_title')"
            :description="__('connections.empty_description')"
            class="mt-8"
        />
    @else
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            @foreach ($providers as $item)
                @php($provider = $item['provider'])
                <x-card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="card-title text-base">{{ $provider->friendly_name }}</h3>

                            <div class="mt-1 flex items-center gap-2">
                                <x-badge :variant="$provider->type === \App\Enums\ApiProviderType::Llm ? 'primary' : 'secondary'">
                                    {{ $provider->type === \App\Enums\ApiProviderType::Llm
                                        ? __('connections.type_llm')
                                        : __('connections.type_media') }}
                                </x-badge>
                                <code class="rounded bg-base-200 px-1.5 py-0.5 text-sm">{{ $provider->machine_name }}</code>
                            </div>
                        </div>

                        @if ($item['connection'])
                            <x-badge variant="success">{{ __('connections.connected') }}</x-badge>
                        @else
                            <x-badge variant="ghost">{{ __('connections.not_connected') }}</x-badge>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('connections.store', $provider) }}" class="mt-4 space-y-3">
                        @csrf

                        @forelse ($provider->meta ?? [] as $field)
                            <x-forms.input
                                :name="'credentials['.$field['name'].']'"
                                type="password"
                                :label="$field['description'] ?: $field['name']"
                                :hint="$field['name']"
                                :error="$errors->first('credentials.'.$field['name'])"
                                autocomplete="off"
                            />
                        @empty
                            <p class="text-sm text-base-content/60">{{ __('connections.no_fields') }}</p>
                        @endforelse

                        <p class="text-xs text-base-content/50">{{ __('connections.credentials_hint') }}</p>

                        <div>
                            <x-button type="submit">
                                {{ $item['connection'] ? __('connections.save') : __('connections.connect') }}
                            </x-button>
                        </div>
                    </form>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>