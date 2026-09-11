<x-layouts.dashboard :title="__('admin.users.add')">
    <x-page-header :title="__('admin.users.add')" :subtitle="__('admin.users.add_subtitle')" />

    <div class="mt-8 max-w-2xl">
        <x-card>
            @include('admin.users._form')
        </x-card>
    </div>
</x-layouts.dashboard>