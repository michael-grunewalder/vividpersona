<button
    type="button"
    x-data="{
        theme: document.documentElement.getAttribute('data-theme'),
        toggle() {
            this.theme = this.theme === 'vividpersona-dark' ? 'vividpersona' : 'vividpersona-dark';
            document.documentElement.setAttribute('data-theme', this.theme);
            localStorage.setItem('theme', this.theme);
        },
    }"
@click="toggle()"
    class="btn btn-ghost btn-circle btn-sm"
    :aria-label="theme === 'vividpersona-dark' ? '{{ __('theme.switch_to_dark') }}' : '{{ __('theme.switch_to_light') }}'"
    :title="theme === 'vividpersona-dark' ? '{{ __('theme.switch_to_dark') }}' : '{{ __('theme.switch_to_light') }}'"
    {{ $attributes }}
>
    <svg class="size-5 block dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
    <svg class="size-5 hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5" />
        <line x1="12" y1="1" x2="12" y2="3" /><line x1="12" y1="21" x2="12" y2="23" />
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" /><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
        <line x1="1" y1="12" x2="3" y2="12" /><line x1="21" y1="12" x2="23" y2="12" />
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" /><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
    </svg>
</button>
