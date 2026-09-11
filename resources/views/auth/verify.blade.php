<x-layouts.app :title="__('auth.verify_title')">
    <section class="flex min-h-[70vh] items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
        <x-card class="w-full max-w-md">
            <div class="space-y-1 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ __('auth.verify_title') }}</h1>
                <p class="text-sm text-base-content/60">{{ __('auth.verify_subtitle', ['email' => $email]) }}</p>
            </div>

            <form
                method="POST"
                action="{{ route('verification.verify') }}"
                class="mt-8 space-y-6"
                x-data="{
                    digits: ['', '', '', '', '', ''],
                    hasError: @json($errors->has('code')),
                    init() {
                        this.$nextTick(() => document.querySelector('input[data-otp]')?.focus());
                    },
                    boxes(event) {
                        return event.target.closest('form').querySelectorAll('input[data-otp]');
                    },
                    submit(event) {
                        this.$refs.code.value = this.digits.join('');
                        event.target.submit();
                    },
                    onInput(event, index) {
                        const value = event.target.value.replace(/\D/g, '').slice(-1);
                        this.digits[index] = value;
                        event.target.value = value;

                        const inputs = this.boxes(event);

                        if (value !== '' && index < this.digits.length - 1 && inputs[index + 1]) {
                            inputs[index + 1].focus();
                        }
                    },
                    onBackspace(event, index) {
                        const inputs = this.boxes(event);

                        if (event.target.value === '' && index > 0 && inputs[index - 1]) {
                            inputs[index - 1].focus();
                        }
                    },
                    onPaste(event) {
                        const inputs = this.boxes(event);
                        const pasted = (event.clipboardData?.getData('text') ?? '').replace(/\D/g, '').slice(0, 6).split('');
                        this.digits = [...pasted, ...Array(6 - pasted.length).fill('')];

                        this.$nextTick(() => {
                            inputs.forEach((el, i) => {
                                el.value = this.digits[i];
                            });

                            inputs[Math.min(pasted.length, 5)]?.focus();
                        });
                    },
                }"
                @submit.prevent="submit($event)"
            >
                @csrf
                <input type="hidden" name="code" x-ref="code" />

                <div class="flex items-center justify-center gap-2 sm:gap-3">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]"
                            maxlength="1"
                            autocomplete="one-time-code"
                            data-otp
                            class="input input-bordered h-14 w-11 text-center text-xl tabular-nums sm:w-12"
                            :class="{ 'input-error': hasError }"
                            :value="digits[{{ $i }}]"
                            @input="onInput($event, {{ $i }})"
                            @keydown.backspace="onBackspace($event, {{ $i }})"
                            @paste.prevent="onPaste($event)"
                        />
                    @endfor
                </div>

                @if ($errors->has('code'))
                    <x-forms.error class="text-center">{{ $errors->first('code') }}</x-forms.error>
                @endif

                <div>
                    <x-button type="submit" class="w-full">{{ __('auth.verify') }}</x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6 text-center">
                @csrf
                <button type="submit" class="link link-hover text-sm">{{ __('auth.request_new_code') }}</button>
            </form>
        </x-card>
    </section>
</x-layouts.app>