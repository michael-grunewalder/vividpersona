@if (session('status') || session('success') || $errors->any())
    <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-cloak class="alert alert-success mb-3">
                <span>{{ session('success') }}</span>
                <button type="button" class="btn btn-ghost btn-xs" @click="show = false" aria-label="Dismiss">✕</button>
            </div>
        @endif

        @if (session('status'))
            <div x-data="{ show: true }" x-show="show" x-cloak class="alert alert-info mb-3">
                <span>{{ session('status') }}</span>
                <button type="button" class="btn btn-ghost btn-xs" @click="show = false" aria-label="Dismiss">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div x-data="{ show: true }" x-show="show" x-cloak class="alert alert-error mb-3">
                <div>
                    <span class="font-semibold">Please fix the following:</span>
                    <ul class="mt-1 list-inside list-disc text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn btn-ghost btn-xs" @click="show = false" aria-label="Dismiss">✕</button>
            </div>
        @endif
    </div>
@endif
