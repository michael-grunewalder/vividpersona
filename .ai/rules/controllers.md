---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Team invitations + base controller authorize
The base App\Http\Controllers\Controller uses the AuthorizesRequests trait, so `$this->authorize('ability', $model)` works (this was missing originally). Team invitations: admins invite by email (pending TeamInvitation, role admin/editor/viewer); an existing user accepts/declines on /teams (authorized by matching email), and RegisteredUserController auto-accepts pending invitations for the new email. Team creators get the global 'owner' role; member roles come from App\Enums\TeamRole.
