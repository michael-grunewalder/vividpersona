<x-layouts.dashboard title="Overview">
    <x-page-header title="Overview" subtitle="Your workspace base is ready to build on.">
        <x-slot:actions>
            <x-button size="sm" variant="outline">Secondary action</x-button>
            <x-button size="sm">Primary action</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <x-card title="Getting started" subtitle="Replace this with your first feature.">
            <p class="text-sm text-base-content/60">
                Compose pages from the shared Blade components and keep the branding in one place.
            </p>
            <x-slot:actions>
                <x-badge variant="primary">New</x-badge>
            </x-slot:actions>
        </x-card>

        <x-card title="Reusable components" subtitle="Buttons, cards, badges and more.">
            <p class="text-sm text-base-content/60">
                Everything lives in <code class="rounded bg-base-200 px-1.5 py-0.5">resources/views/components</code>.
            </p>
        </x-card>

        <x-card title="Themed" subtitle="Light and dark out of the box.">
            <p class="text-sm text-base-content/60">
                Toggle the theme from the header — the choice is remembered between visits.
            </p>
        </x-card>
    </div>

    <div class="mt-6">
        <x-empty-state
            title="Nothing here yet"
            description="This dashboard shell is ready for your application's pages."
        >
            <x-slot:icon>
                <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                </svg>
            </x-slot:icon>
            <x-slot:action>
                <x-button size="sm" variant="outline">Create something</x-button>
            </x-slot:action>
        </x-empty-state>
    </div>
</x-layouts.dashboard>
