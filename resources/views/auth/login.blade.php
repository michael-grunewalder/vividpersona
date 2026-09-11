<x-layouts.app :title="__('auth.login_title')">
    <section class="flex min-h-[70vh] items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
        <x-card class="w-full max-w-md">
            <div class="space-y-1 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ __('auth.login_title') }}</h1>
                <p class="text-sm text-base-content/60">{{ __('auth.login_subtitle') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <x-forms.input
                    name="email"
                    type="email"
                    :label="__('auth.email')"
                    :value="old('email')"
                    :error="$errors->first('email')"
                    required
                    autofocus
                    autocomplete="email"
                />

                <x-forms.input
                    name="password"
                    type="password"
                    :label="__('auth.password')"
                    :error="$errors->first('password')"
                    required
                    autocomplete="current-password"
                />

                <label class="flex cursor-pointer items-center gap-2 text-sm text-base-content/70">
                    <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                    {{ __('auth.remember_me') }}
                </label>

                <div>
                    <x-button type="submit" class="w-full">{{ __('auth.login') }}</x-button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-base-content/60">
                {{ __('auth.not_registered') }}
                <a href="{{ route('register') }}" class="link font-medium">{{ __('auth.register') }}</a>
            </p>
        </x-card>
    </section>
</x-layouts.app>