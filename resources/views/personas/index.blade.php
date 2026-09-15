<x-layouts.dashboard :title="__('personas.title')">
    <x-page-header :title="__('personas.title')" :subtitle="__('personas.subtitle')">
        <x-slot:actions>
            <x-button :href="route('personas.create')">{{ __('personas.create') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($personas->isEmpty())
        <x-empty-state
            :title="__('personas.empty_title')"
            :description="__('personas.empty_description')"
            class="mt-8"
        >
            <x-slot:action>
                <x-button :href="route('personas.create')">{{ __('personas.create') }}</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($personas as $persona)
                <div class="card border border-base-300 bg-base-100 shadow-sm transition hover:border-primary/50">
                    <a href="{{ route('personas.show', $persona) }}" class="card-body block">
                        <div class="flex items-center gap-3">
                            @if ($persona->imageUrl())
                                <img src="{{ $persona->imageUrl() }}" alt="" class="size-14 rounded-xl object-cover" />
                            @else
                                <span class="grid size-14 place-items-center rounded-xl bg-base-200 text-lg font-bold">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($persona->name, 0, 2)) }}
                                </span>
                            @endif

                            <div class="min-w-0">
                                <h3 class="card-title truncate text-base">{{ $persona->name }}</h3>
                                <p class="text-sm text-base-content/60">{{ $persona->gender }}, {{ $persona->age }}</p>
                            </div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-badge :variant="$persona->status === 'ready' ? 'success' : 'ghost'">
                                {{ __('personas.status_'.$persona->status) }}
                            </x-badge>
                            <x-badge variant="ghost">
                                {{ count($persona->generation_history ?? []) }} {{ __('personas.sets') }}
                            </x-badge>
                        </div>
                    </a>

                    <form
                        method="POST"
                        action="{{ route('personas.destroy', $persona) }}"
                        onsubmit="return confirm('{{ __('personas.delete_confirm') }}')"
                        class="px-4 pb-4"
                    >
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="ghost" size="sm">{{ __('personas.delete') }}</x-button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>