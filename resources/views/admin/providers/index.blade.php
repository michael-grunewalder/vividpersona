<x-layouts.dashboard :title="__('admin.providers.title')">
    <x-page-header :title="__('admin.providers.title')" :subtitle="__('admin.providers.subtitle')">
        <x-slot:actions>
            <x-button :href="route('backend.api-provider.create')">{{ __('admin.providers.add') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($providers->isEmpty())
        <x-empty-state
            :title="__('admin.providers.empty_title')"
            :description="__('admin.providers.empty_description')"
            class="mt-8"
        >
            <x-slot:action>
                <x-button :href="route('backend.api-provider.create')">{{ __('admin.providers.add') }}</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-8 overflow-x-auto rounded-box border border-base-300 bg-base-100 shadow-sm">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('admin.providers.name_col') }}</th>
                        <th>{{ __('admin.providers.type') }}</th>
                        <th>{{ __('admin.providers.fields_col') }}</th>
                        <th>{{ __('admin.providers.machine_name') }}</th>
                        <th>{{ __('admin.providers.base_url') }}</th>
                        <th class="text-right">{{ __('admin.providers.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($providers as $provider)
                        <tr>
                            <td class="font-medium">{{ $provider->friendly_name }}</td>
                            <td>
                                <x-badge :variant="$provider->type === \App\Enums\ApiProviderType::Llm ? 'primary' : 'secondary'">
                                    {{ $provider->type === \App\Enums\ApiProviderType::Llm
                                        ? __('admin.providers.type_llm')
                                        : __('admin.providers.type_media') }}
                                </x-badge>
                            </td>
                            <td>
                                <x-badge variant="ghost">{{ count($provider->meta ?? []) }}</x-badge>
                            </td>
                            <td>
                                <code class="rounded bg-base-200 px-1.5 py-0.5 text-sm">{{ $provider->machine_name }}</code>
                            </td>
                            <td class="text-base-content/60">{{ $provider->base_url }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <x-button :href="route('backend.api-provider.edit', $provider)" size="xs" variant="outline">
                                        {{ __('admin.providers.edit') }}
                                    </x-button>

                                    <form
                                        method="POST"
                                        action="{{ route('backend.api-provider.destroy', $provider) }}"
                                        onsubmit="return confirm('{{ __('admin.providers.delete_confirm') }}')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" size="xs" variant="error">{{ __('admin.providers.delete') }}</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.dashboard>