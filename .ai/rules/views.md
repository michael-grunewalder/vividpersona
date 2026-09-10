---
paths:
  - 'resources/views/**'
---

# Views

## Never build Tailwind class names dynamically
Tailwind v4 scans Blade files for literal class strings. `class="bg-{{ $var }}"` produces no CSS because the compiled class never appears literally. Always write full class strings (e.g. store `'bg-primary'` in an array in the Blade file) or use component props that map to literal classes (see components/button, card, badge).
