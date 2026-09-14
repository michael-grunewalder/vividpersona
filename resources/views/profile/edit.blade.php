<x-layouts.dashboard :title="__('profile.title')">
    <x-page-header :title="__('profile.title')" :subtitle="__('profile.subtitle')" />

    <div class="mt-8 max-w-2xl">
        <x-card :title="__('profile.details_title')">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PATCH')

                <x-forms.input
                    name="name"
                    :label="__('profile.name')"
                    :value="old('name', $user->name)"
                    :error="$errors->first('name')"
                    required
                    autocomplete="name"
                />

                <x-forms.input
                    name="email"
                    type="email"
                    :label="__('profile.email')"
                    :value="old('email', $user->email)"
                    :error="$errors->first('email')"
                    required
                    autocomplete="email"
                />

                <div class="space-y-1.5">
                    <x-forms.label>{{ __('profile.avatar') }}</x-forms.label>

                    <div class="flex items-center gap-4">
                        <span class="relative grid size-16 shrink-0 place-items-center overflow-hidden rounded-full bg-gradient-to-br from-primary to-secondary text-lg font-bold text-primary-content">
                            {{ $user->initials() }}

                            @if ($user->avatarUrl())
                                <img src="{{ $user->avatarUrl() }}" alt="" class="absolute inset-0 size-full object-cover" onerror="this.remove()" />
                            @endif
                        </span>

                        <div class="space-y-2">
                            <input type="file" name="avatar" accept="image/*" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />

                            @if ($user->avatar_path)
                                <label class="flex items-center gap-2 text-sm text-base-content/70">
                                    <input type="checkbox" name="remove_avatar" value="1" class="checkbox checkbox-sm" />
                                    {{ __('profile.avatar_remove') }}
                                </label>
                            @endif
                        </div>
                    </div>

                    @if ($errors->has('avatar'))
                        <x-forms.error>{{ $errors->first('avatar') }}</x-forms.error>
                    @endif
                </div>

                <div class="border-t border-base-300 pt-4">
                    <h3 class="text-base font-semibold">{{ __('profile.password_title') }}</h3>
                    <p class="text-xs text-base-content/50">{{ __('profile.password_hint') }}</p>
                </div>

                <x-forms.input
                    name="current_password"
                    type="password"
                    :label="__('profile.current_password')"
                    :error="$errors->first('current_password')"
                    autocomplete="current-password"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-forms.input
                        name="password"
                        type="password"
                        :label="__('profile.new_password')"
                        :error="$errors->first('password')"
                        autocomplete="new-password"
                    />

                    <x-forms.input
                        name="password_confirmation"
                        type="password"
                        :label="__('profile.password_confirmation')"
                        autocomplete="new-password"
                    />
                </div>

                <div>
                    <x-button type="submit">{{ __('profile.save') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.dashboard>