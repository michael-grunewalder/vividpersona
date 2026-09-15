---
paths:
  - 'app/Http/Controllers/**'
  - app/Http/Controllers/PersonaController.php
---

# Controllers

## Team invitations + base controller authorize
The base App\Http\Controllers\Controller uses the AuthorizesRequests trait, so `$this->authorize('ability', $model)` works (this was missing originally). Team invitations: admins invite by email (pending TeamInvitation, role admin/editor/viewer); an existing user accepts/declines on /teams (authorized by matching email), and RegisteredUserController auto-accepts pending invitations for the new email. Team creators get the global 'owner' role; member roles come from App\Enums\TeamRole.

## Choosing locks the persona to its single reference image
Choosing an image locks the persona: `choose()` downloads the selected image into `media/images`, sets `main_image` + `reference_image_path`, clears `generation_history`, and deletes the uploaded face/style refs (only the chosen image remains). Once a reference is set, `generateSet`/`choose` are rejected and the show page hides "generate another set" — a persona is locked with its single reference image. Reference uploads in `store()` go to the private `media/images` folder (relative paths), not the public disk.
