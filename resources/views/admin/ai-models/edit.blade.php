<x-layouts.dashboard :title="__('admin.ai_models.edit_title')">
    <x-page-header :title="__('admin.ai_models.edit_title')" :subtitle="__('admin.ai_models.edit_subtitle', ['name' => $model->label()])" />

    <div class="mt-8 max-w-3xl">
        <x-card>
            @include('admin.ai-models._form', ['model' => $model])
        </x-card>
    </div>
</x-layouts.dashboard>