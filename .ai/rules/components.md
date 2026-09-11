---
paths:
  - 'resources/views/components/**'
---

# Components

## Avoid @elseauth; it never renders for guests
Never use `@auth ... @elseauth` in Blade. Laravel compiles `@elseauth` to `elseif(auth()->guard()->check())`, which is always false for guests, so the guest branch silently renders nothing. Use `@auth ... @else ... @endauth` (or `@guest`/`@else`) instead.
