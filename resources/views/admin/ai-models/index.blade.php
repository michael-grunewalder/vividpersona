<x-layouts.dashboard :title="__('admin.ai_models.title')">
    <x-page-header :title="__('admin.ai_models.title')" :subtitle="__('admin.ai_models.subtitle')">
        <x-slot:actions>
            <x-button :href="route('backend.ai-model.create')">{{ __('admin.ai_models.add') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($models->isEmpty())
        <x-empty-state
            :title="__('admin.ai_models.empty_title')"
            :description="__('admin.ai_models.empty_description')"
            class="mt-4"
        />
    @else
        <div class="mt-4 overflow-x-auto rounded-box border border-base-300 bg-base-100 shadow-sm">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('admin.ai_models.type') }}</th>
                        <th>{{ __('admin.ai_models.model') }}</th>
                        <th>{{ __('admin.ai_models.version') }}</th>
                        <th>{{ __('admin.ai_models.variant') }}</th>
                        <th>{{ __('admin.ai_models.provider') }}</th>
                        <th>{{ __('admin.ai_models.endpoint') }}</th>
                        <th>{{ __('admin.ai_models.personas') }}</th>
                        <th>{{ __('admin.ai_models.status') }}</th>
                        <th class="text-right">{{ __('admin.ai_models.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($models as $model)
                        <tr>
                            <td>
                                <x-badge variant="ghost">{{ $model->type->label() }}</x-badge>
                            </td>
                            <td class="font-medium">
                                {{ $model->name }}
                                <span class="text-base-content/50">({{ $model->family }})</span>
                            </td>
                            <td>{{ $model->version ?? '—' }}</td>
                            <td>{{ $model->variant ?? '—' }}</td>
                            <td>{{ $model->provider->label() }}</td>
                            <td class="max-w-[16rem] truncate font-mono text-xs" title="{{ $model->endpoint }}">{{ $model->endpoint }}</td>
                            <td>{{ $model->personas_count }}</td>
                            <td>
                                @if ($model->enabled)
                                    <x-badge variant="success">{{ __('admin.ai_models.enabled_label') }}</x-badge>
                                @else
                                    <x-badge variant="ghost">{{ __('admin.ai_models.disabled_label') }}</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <x-button :href="route('backend.ai-model.edit', $model)" variant="outline" size="sm">
                                        {{ __('admin.ai_models.edit') }}
                                    </x-button>

                                    <form
                                        method="POST"
                                        action="{{ route('backend.ai-model.destroy', $model) }}"
                                        onsubmit="return confirm('{{ __('admin.ai_models.delete_confirm') }}')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="error" size="sm">{{ __('admin.ai_models.delete') }}</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $models->links() }}
        </div>
    @endif
</x-layouts.dashboard>