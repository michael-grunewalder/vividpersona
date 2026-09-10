# VividPersona

A modern Laravel base template built with **Tailwind CSS v4**, **DaisyUI 5** and
**Alpine.js** — themed, responsive and ready for real screens.

## Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 13 (PHP 8.5) |
| Styling | Tailwind CSS v4 (CSS-first config) |
| Components | DaisyUI 5 |
| Interactivity | Alpine.js 3 |
| Bundler | Vite 8 |
| Tests | Pest |

## Requirements

- PHP 8.5+
- Composer
- Node.js 20+ and npm

## Getting started

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
```

Run the app (Vite dev server + queue + logs in one command):

```bash
composer run dev
```

Or run the pieces separately:

```bash
php artisan serve
npm run dev
```

Build production assets:

```bash
npm run build
```

## Project structure

```
resources/
  css/app.css                     Tailwind + DaisyUI themes (branding lives here)
  js/app.js                       Alpine.js entry point
  views/
    landing.blade.php             Public landing page
    dashboard.blade.php           Dashboard placeholder page
    components/
      layouts/head.blade.php      Shared <head> (meta, theme prepaint, @vite)
      layouts/app.blade.php       Public shell: navbar + content + footer
      layouts/dashboard.blade.php Dashboard shell: sidebar drawer + header
      navbar.blade.php
      sidebar.blade.php
      footer.blade.php
      flash.blade.php             Session/validation alerts
      theme-toggle.blade.php      Light/dark switch (persisted)
      button.blade.php
      card.blade.php
      badge.blade.php
      page-header.blade.php
      empty-state.blade.php
routes/web.php                    home + dashboard routes
```

## Adjusting branding colors

All branding is defined in **`resources/css/app.css`** using DaisyUI theme
tokens. There are two theme blocks:

- `vividpersona` — the light theme (`default: true`)
- `vividpersona-dark` — the dark theme (`prefersdark: true`)

Change the values inside those blocks and every component updates automatically.

```css
@plugin "daisyui/theme" {
    name: "vividpersona";
    default: true;
    color-scheme: light;

    --color-primary: oklch(58% 0.235 295); /* main brand color */
    --color-secondary: oklch(63% 0.24 340); /* supporting color */
    --color-accent: oklch(70% 0.15 230); /* highlight color */
    /* ... */
}
```

### Token reference

| Token | Used for |
| --- | --- |
| `--color-primary` / `--color-primary-content` | Primary buttons, links, brand accents |
| `--color-secondary` / `--color-secondary-content` | Secondary accents and gradients |
| `--color-accent` / `--color-accent-content` | Highlights and badges |
| `--color-neutral` / `--color-neutral-content` | Neutral surfaces and text |
| `--color-base-100` | Page background |
| `--color-base-200` | Subtle raised sections |
| `--color-base-300` | Borders and stronger surfaces |
| `--color-base-content` | Default text color |
| `--color-info` / `--color-success` / `--color-warning` / `--color-error` | Status alerts |

Each color also has a matching `*-content` token that controls the readable text
color placed on top of it.

Colors are written in [`oklch()`](https://oklch.com/), but any valid CSS color
(e.g. `#7c3aed`) works too. Change both theme blocks to keep light and dark in
sync.

### Other theme knobs

Inside each theme block you can also adjust:

| Token | Controls |
| --- | --- |
| `--radius-box` | Cards and larger containers |
| `--radius-field` | Inputs and buttons |
| `--radius-selector` | Checkboxes, toggles, badges |
| `--border` | Global border width |
| `--depth` / `--noise` | Subtle depth effects (0 = flat) |

### Renaming the themes

The theme names are referenced in a few places. If you rename `vividpersona`
and `vividpersona-dark`, update all of them:

- `resources/css/app.css` — the `name:` of each `@plugin "daisyui/theme"` block
- `resources/views/components/layouts/head.blade.php` — the theme names in the
  prepaint script
- `resources/views/components/layouts/app.blade.php` — the `<html data-theme>`
  default
- `resources/views/components/layouts/dashboard.blade.php` — the
  `<html data-theme>` default
- `resources/views/components/theme-toggle.blade.php` — the toggle logic

### Adding more themes

Add another `@plugin "daisyui/theme"` block with a unique `name` and set
`data-theme="your-theme"` on the `<html>` element (or any element) to use it.
See the [DaisyUI themes documentation](https://daisyui.com/docs/themes/).

## Using the components

```blade
<x-button :href="route('dashboard')">Open dashboard</x-button>
<x-button variant="outline" size="sm">Secondary</x-button>

<x-card title="Title" subtitle="Supporting text">
    <p>Card body.</p>
    <x-slot:actions>
        <x-badge variant="primary">New</x-badge>
    </x-slot:actions>
</x-card>

<x-page-header title="Overview" subtitle="Workspace summary">
    <x-slot:actions>
        <x-button size="sm">Action</x-button>
    </x-slot:actions>
</x-page-header>

<x-empty-state title="Nothing here yet" description="Add your first item." />
```

Layouts are used as components:

```blade
<x-layouts.app title="Page title">...</x-layouts.app>
<x-layouts.dashboard title="Overview">...</x-layouts.dashboard>
```

## Theming behaviour

The saved theme is stored in `localStorage` under the `theme` key. On first
visit the app falls back to the operating system preference. An inline script in
`components/layouts/head.blade.php` applies the theme before first paint to
avoid a flash of the wrong theme.

## Testing and formatting

```bash
php artisan test --compact   # run the test suite
vendor/bin/pint --dirty      # format changed PHP files
```
