@props(['model' => null])

@php
    $isEdit = ! is_null($model);
    $action = $isEdit
        ? route('backend.ai-model.update', $model)
        : route('backend.ai-model.store');
    $optionsJson = $isEdit && is_array($model->options)
        ? json_encode($model->options, JSON_PRETTY_PRINT)
        : '';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.select
            name="type"
            :label="__('admin.ai_models.type')"
            :options="collect(\App\Enums\AiModelType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
            :selected="old('type', $isEdit ? $model->type->value : 'image')"
            :error="$errors->first('type')"
            required
        />

        <x-forms.select
            name="provider"
            :label="__('admin.ai_models.provider')"
            :options="collect(\App\Enums\ApiService::media())->mapWithKeys(fn ($service) => [$service->value => $service->label()])->all()"
            :selected="old('provider', $isEdit ? $model->provider->value : null)"
            :error="$errors->first('provider')"
            required
        />

        <x-forms.input
            name="family"
            :label="__('admin.ai_models.family')"
            :hint="__('admin.ai_models.family_hint')"
            :value="old('family', $isEdit ? $model->family : '')"
            :error="$errors->first('family')"
            required
        />

        <x-forms.input
            name="name"
            :label="__('admin.ai_models.name')"
            :value="old('name', $isEdit ? $model->name : '')"
            :error="$errors->first('name')"
            required
        />

        <x-forms.input
            name="version"
            :label="__('admin.ai_models.version')"
            :hint="__('admin.ai_models.version_hint')"
            :value="old('version', $isEdit ? $model->version : '')"
            :error="$errors->first('version')"
        />

        <x-forms.input
            name="variant"
            :label="__('admin.ai_models.variant')"
            :hint="__('admin.ai_models.variant_hint')"
            :value="old('variant', $isEdit ? $model->variant : '')"
            :error="$errors->first('variant')"
        />

        <x-forms.input
            name="endpoint"
            :label="__('admin.ai_models.endpoint')"
            :hint="__('admin.ai_models.endpoint_hint')"
            :value="old('endpoint', $isEdit ? $model->endpoint : '')"
            :error="$errors->first('endpoint')"
            required
        />

        <div class="space-y-1.5 sm:col-span-2">
            <x-forms.label>{{ __('admin.ai_models.options') }}</x-forms.label>
            <textarea
                name="options"
                rows="7"
                class="textarea textarea-bordered w-full font-mono text-xs"
                placeholder='{ "size": { "param": "image_size", "sizes": { "9:16": "portrait_16_9", "16:9": "landscape_16_9" } }, "defaults": { "num_images": 1, "output_format": "png" } }'
            >{{ old('options', $optionsJson) }}</textarea>
            <p class="text-xs text-base-content/50">{{ __('admin.ai_models.options_hint') }}</p>
            @if ($errors->has('options'))
                <x-forms.error>{{ $errors->first('options') }}</x-forms.error>
            @endif
        </div>

        <x-forms.input
            name="sort"
            type="number"
            min="0"
            :label="__('admin.ai_models.sort')"
            :hint="__('admin.ai_models.sort_hint')"
            :value="old('sort', $isEdit ? $model->sort : 0)"
            :error="$errors->first('sort')"
        />
    </div>

    <x-forms.checkbox
        name="enabled"
        :label="__('admin.ai_models.enabled')"
        :hint="__('admin.ai_models.enabled_hint')"
        :checked="old('enabled', $isEdit ? $model->enabled : true)"
        :error="$errors->first('enabled')"
    />

    <div class="flex items-center gap-3 pt-2">
        <x-button type="submit">{{ __('admin.ai_models.save') }}</x-button>
        <x-button :href="route('backend.ai-model.index')" variant="ghost">{{ __('admin.ai_models.cancel') }}</x-button>
    </div>
</form>