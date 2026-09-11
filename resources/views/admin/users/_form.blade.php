@props(['user' => null])

@php
    $isEdit = ! is_null($user);
    $action = $isEdit
        ? route('backend.user.update', $user)
        : route('backend.user.store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    <x-forms.input
        name="name"
        :label="__('admin.users.name')"
        :value="old('name', $isEdit ? $user->name : '')"
        :error="$errors->first('name')"
        autocomplete="off"
        required
    />

    <x-forms.input
        name="email"
        type="email"
        :label="__('admin.users.email')"
        :value="old('email', $isEdit ? $user->email : '')"
        :error="$errors->first('email')"
        autocomplete="off"
        required
    />

    <x-forms.input
        name="password"
        type="password"
        :label="__('admin.users.password')"
        :hint="$isEdit ? __('admin.users.password_hint') : null"
        :error="$errors->first('password')"
        autocomplete="new-password"
        @required(! $isEdit)
    />

    <x-forms.select
        name="plan"
        :label="__('admin.users.plan')"
        :options="collect(\App\Enums\Plan::cases())->mapWithKeys(fn ($plan) => [$plan->value => $plan->label()])->all()"
        :selected="old('plan', $isEdit ? $user->meta->currentPlan->value : null)"
        :error="$errors->first('plan')"
        required
    />

    <x-forms.input
        name="max_teams"
        type="number"
        min="1"
        :label="__('admin.users.max_teams')"
        :value="old('max_teams', $isEdit ? $user->meta->maxTeams : 1)"
        :error="$errors->first('max_teams')"
        required
    />

    <x-forms.checkbox
        name="is_super_admin"
        :label="__('admin.users.super_admin')"
        :hint="__('admin.users.super_admin_hint')"
        :checked="old('is_super_admin', $isEdit ? $user->is_super_admin : false)"
        :error="$errors->first('is_super_admin')"
    />

    <div class="flex items-center gap-3 pt-2">
        <x-button type="submit">{{ __('admin.users.save') }}</x-button>
        <x-button :href="route('backend.user.index')" variant="ghost">{{ __('admin.users.cancel') }}</x-button>
    </div>
</form>