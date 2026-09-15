---
paths:
  - 'app/Models/**'
  - app/Models/Persona.php
---

# Models

## Roles are global; assignment is team-scoped
Roles are global: RolePermissionSeeder creates each role once with team_id null, so a role has identical permissions in every team. Per-team differences exist only in assignment (model_has_roles.team_id), never in the role's permission set. Add or rename roles/permissions only in RolePermissionSeeder, never per-team.

## Avatar: upload → Gravatar → initials fallback
User avatar resolution: `User::avatarUrl()` returns the uploaded avatar (Storage public URL) if `avatar_path` is set, otherwise the Gravatar URL with `d=404`; the view shows initials behind an `<img onerror="this.remove()">` so a missing Gravatar falls back to the gradient initials circle. Avatar uploads go to `avatars` on the public disk — deployments must run `php artisan storage:link`.

## Persona private media + 20-min signed reference links
All persona images live in the `private` disk (root `storage_path('private')`) under `{team_id}/personas/{persona_id}/media/images` (`Persona::mediaFolder()`). Private files are shared with external image APIs via `Persona::mediaUrl()` / `referenceImageUrl()`: a `URL::temporarySignedRoute('personas.media', now()->addMinutes(20), [persona, path])` link served by `PersonaMediaController` (validates signature + that the path is inside the persona's media folder). Never use the `public` disk for persona images. Signed URLs expire after 20 minutes.
