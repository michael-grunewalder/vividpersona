<x-layouts.dashboard :title="__('admin.ai_models.add')">
    <x-page-header :title="__('admin.ai_models.add')" :subtitle="__('admin.ai_models.add_subtitle')" />

    <div class="mt-8 max-w-3xl">
        <x-card>
            @include('admin.ai-models._form')
        </x-card>
    </div>
</x-layouts.dashboard>