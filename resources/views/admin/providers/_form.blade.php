@props(['provider' => null])

@php
    $isEdit = ! is_null($provider);
    $action = $isEdit
        ? route('backend.api-provider.update', $provider)
        : route('backend.api-provider.store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    <x-forms.select
        name="type"
        :label="__('admin.providers.type')"
        :options="[
            \App\Enums\ApiProviderType::Llm->value => __('admin.providers.type_llm'),
            \App\Enums\ApiProviderType::Media->value => __('admin.providers.type_media'),
        ]"
        :selected="old('type', $isEdit ? $provider->type->value : null)"
        :error="$errors->first('type')"
        required
    />

    <x-forms.input
        name="machine_name"
        :label="__('admin.providers.machine_name')"
        :hint="__('admin.providers.machine_name_hint')"
        :value="old('machine_name', $isEdit ? $provider->machine_name : '')"
        :error="$errors->first('machine_name')"
        autocomplete="off"
        required
    />

    <x-forms.input
        name="friendly_name"
        :label="__('admin.providers.friendly_name')"
        :value="old('friendly_name', $isEdit ? $provider->friendly_name : '')"
        :error="$errors->first('friendly_name')"
        required
    />

    <x-forms.input
        name="base_url"
        :label="__('admin.providers.base_url')"
        :value="old('base_url', $isEdit ? $provider->base_url : '')"
        :error="$errors->first('base_url')"
        autocomplete="off"
    />

    <div
        x-data="{
            fields: {{ Illuminate\Support\Js::from(old('meta', $isEdit ? $provider->meta ?? [] : [])) }},
            add() {
                this.fields.push({ name: '', description: '' });
            },
            remove(index) {
                this.fields.splice(index, 1);
            },
        }"
        class="space-y-2"
    >
        <div class="space-y-1">
            <x-forms.label>{{ __('admin.providers.meta_title') }}</x-forms.label>
            <p class="text-xs text-base-content/50">{{ __('admin.providers.meta_hint') }}</p>
        </div>

        <template x-for="(field, index) in fields" :key="index">
            <div class="flex items-start gap-2">
                <input
                    type="text"
                    :name="'meta['+index+'][name]'"
                    x-model="field.name"
                    class="input input-bordered w-40 font-mono text-sm"
                    :placeholder="'{{ __('admin.providers.meta_name') }}'"
                />
                <input
                    type="text"
                    :name="'meta['+index+'][description]'"
                    x-model="field.description"
                    class="input input-bordered flex-1 text-sm"
                    :placeholder="'{{ __('admin.providers.meta_description') }}'"
                />
                <button
                    type="button"
                    class="btn btn-ghost btn-square btn-sm"
                    @click="remove(index)"
                    :aria-label="'{{ __('admin.providers.meta_remove') }}'"
                >✕</button>
            </div>
        </template>

        <button type="button" class="btn btn-outline btn-sm" @click="add()">
            + {{ __('admin.providers.meta_add') }}
        </button>

        @php
            $metaErrors = collect($errors->getMessages())
                ->filter(fn (array $messages, string $key) => str_starts_with($key, 'meta'));
        @endphp

        @if ($metaErrors->isNotEmpty())
            <x-forms.error>{{ $metaErrors->flatten()->first() }}</x-forms.error>
        @endif
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button type="submit">{{ __('admin.providers.save') }}</x-button>
        <x-button :href="route('backend.api-provider.index')" variant="ghost">{{ __('admin.providers.cancel') }}</x-button>
    </div>
</form>