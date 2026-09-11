<x-layouts.app :title="__('auth.register_title')">
    <section class="flex min-h-[70vh] items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
        <x-card class="w-full max-w-md">
            <div class="space-y-1 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ __('auth.register_title') }}</h1>
                <p class="text-sm text-base-content/60">
                    {{ $invitation
                        ? __('auth.register_invited_subtitle', ['team' => $invitation->team->name])
                        : __('auth.register_subtitle') }}
                </p>
            </div>

            @if ($invitation)
                <div class="alert alert-info mt-6 text-sm">
                    {{ __('auth.register_invited_hint', ['team' => $invitation->team->name, 'role' => $invitation->role->label()]) }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf

                <x-forms.input
                    name="name"
                    type="text"
                    :label="__('auth.name')"
                    :value="old('name', $invitation?->name ?? '')"
                    :error="$errors->first('name')"
                    required
                    autofocus
                    autocomplete="name"
                />

                <x-forms.input
                    name="email"
                    type="email"
                    :label="__('auth.email')"
                    :value="old('email', $invitation?->email ?? '')"
                    :error="$errors->first('email')"
                    required
                    autocomplete="email"
                />

                <x-forms.input
                    name="password"
                    type="password"
                    :label="__('auth.password')"
                    :error="$errors->first('password')"
                    required
                    autocomplete="new-password"
                />

                <x-forms.input
                    name="password_confirmation"
                    type="password"
                    :label="__('auth.password_confirmation')"
                    required
                    autocomplete="new-password"
                />

                <div>
                    <x-button type="submit" class="w-full">{{ __('auth.create_account') }}</x-button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-base-content/60">
                {{ __('auth.already_registered') }}
                <a href="{{ route('login') }}" class="link font-medium">{{ __('auth.sign_in') }}</a>
            </p>
        </x-card>
    </section>
</x-layouts.app>