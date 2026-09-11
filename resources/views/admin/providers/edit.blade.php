<x-layouts.dashboard :title="__('admin.providers.edit_title')">
    <x-page-header :title="__('admin.providers.edit_title')" :subtitle="__('admin.providers.edit_subtitle', ['name' => $provider->friendly_name])" />

    <div class="mt-8 max-w-2xl">
        <x-card>
            @include('admin.providers._form', ['provider' => $provider])
        </x-card>
    </div>
</x-layouts.dashboard>