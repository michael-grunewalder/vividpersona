<x-layouts.dashboard :title="__('admin.providers.add')">
    <x-page-header :title="__('admin.providers.add')" :subtitle="__('admin.providers.add_subtitle')" />

    <div class="mt-8 max-w-2xl">
        <x-card>
            @include('admin.providers._form')
        </x-card>
    </div>
</x-layouts.dashboard>