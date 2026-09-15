@php($service = $item['service'])

<x-card>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="card-title text-base">{{ $service->label() }}</h3>

            <div class="mt-1 flex items-center gap-2">
                <x-badge variant="secondary">
                    {{ $service->isLlm() ? __('connections.type_llm') : __('connections.type_media') }}
                </x-badge>
                <code class="rounded bg-base-200 px-1.5 py-0.5 text-sm">{{ $service->value }}</code>
            </div>
        </div>

        @if ($item['connected'])
            <x-badge variant="success">{{ __('connections.connected') }}</x-badge>
        @else
            <x-badge variant="ghost">{{ __('connections.not_connected') }}</x-badge>
        @endif
    </div>

    <form method="POST" action="{{ route('connections.store', $service->value) }}" class="mt-4 space-y-3">
        @csrf

        @foreach ($service->credentialFields() as $field)
            <x-forms.input
                :name="$service->value.'['.$field.']'"
                type="password"
                :label="__('connections.field_'.$field)"
                :error="$errors->first($service->value.'.'.$field)"
                autocomplete="off"
            />
        @endforeach

        <p class="text-xs text-base-content/50">{{ __('connections.credentials_hint') }}</p>

        <div class="flex items-center gap-2">
            <x-button type="submit">
                {{ $item['connected'] ? __('connections.save') : __('connections.connect') }}
            </x-button>

            @if ($item['connected'])
                <form
                    method="POST"
                    action="{{ route('connections.disconnect', $service->value) }}"
                    onsubmit="return confirm('{{ __('connections.disconnect_confirm') }}')"
                >
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="ghost" size="sm">{{ __('connections.disconnect') }}</x-button>
                </form>
            @endif
        </div>
    </form>
</x-card>