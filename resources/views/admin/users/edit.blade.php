<x-layouts.dashboard :title="__('admin.users.edit_title')">
    <x-page-header :title="__('admin.users.edit_title')" :subtitle="__('admin.users.edit_subtitle', ['name' => $user->name])" />

    <div class="mt-8 max-w-2xl">
        <x-card>
            @include('admin.users._form', ['user' => $user])
        </x-card>
    </div>
</x-layouts.dashboard>