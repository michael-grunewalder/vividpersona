---
paths:
  - 'resources/views/**'
---

# Views

## Never build Tailwind class names dynamically
Tailwind v4 scans Blade files for literal class strings. `class="bg-{{ $var }}"` produces no CSS because the compiled class never appears literally. Always write full class strings (e.g. store `'bg-primary'` in an array in the Blade file) or use component props that map to literal classes (see components/button, card, badge).

## Avoid x-ref inside x-for; query elements instead
In this Alpine version, `x-ref="foo"` inside `x-for` does NOT collect into an array (later iterations overwrite), so `$refs.foo[i]` can be undefined and throw. For repeated interactive inputs, query them instead: `this.$el.querySelectorAll('input[data-otp]')` (indexed NodeList), and render with a Blade `@for` loop rather than `x-for` when refs are needed.

## Focus repeated inputs from event.target, not $el/$refs
For repeated interactive inputs (e.g. OTP boxes), Alpine `$refs` inside `x-for` AND `this.$el`-based `querySelectorAll` both proved unreliable in this app (silently resolving to undefined). The robust pattern is to walk the DOM from the event: `event.target.closest('form').querySelectorAll('input[data-otp]')`, and guard every `.focus()` with an existence check.

## Bare @elsecan crashes; use @else
A bare `@elsecan` (no arguments) compiles to `elseif (app(Gate::class)->check)` — `->check` without parentheses becomes a property access → `Undefined property: Gate::$check`. For the else branch of `@can(...)`, use plain `@else`. Same family as the `@elseauth` trap.

## Use Js::from (not @json) inside HTML attributes
Never inject `@json($arrayOrObject)` into a double-quoted HTML attribute (e.g. Alpine `x-data`): json_encode leaves structural quotes raw, terminating the attribute and silently killing the Alpine component. Use `{{ Illuminate\Support\Js::from($value) }}` instead (emits HTML-safe `JSON.parse('...')`). Bare booleans/numbers via `@json` are fine.

## Never call __() inside Alpine :bindings on plain HTML elements
On a plain HTML element, `:attr="..."` is an Alpine binding evaluated in JS, so `__()` (a PHP function) throws `ReferenceError: __ is not defined` and breaks the whole Alpine component (symptoms: wizard buttons/clicks stop working). Only Blade components (`<x-... :prop="__()">`) treat a leading colon as a server-side prop binding. For plain elements use `attr="{{ __('...') }}"`. To interpolate a PHP string into an Alpine expression, embed it as `'{{ __('...') }}'` (JS string), not a bare call.
