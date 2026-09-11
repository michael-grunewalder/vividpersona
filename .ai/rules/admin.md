---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## API provider fields are definitions, not secrets
ApiProvider stores credential field DEFINITIONS in a JSON `meta` column (array of `{ name, description }`), where `name` is the library key (snake-case, unique) and `description` is the help text shown to the end user. There are no secret columns — the actual credential values are supplied by end users elsewhere. The SaveApiProviderRequest validates `meta` (drops fully-empty rows in prepareForValidation; `meta.*.name` required/snake/distinct, `meta.*.description` required) and the admin form renders dynamic rows with Alpine (`meta[][name]` / `meta[][description]`).

## User manager safety guards and forceFill
User manager safety: users who own teams cannot be deleted; you cannot delete yourself or revoke your own super-admin, and the last super-admin cannot be revoked. `is_super_admin` and `email_verified_at` are intentionally NOT fillable — set them via forceFill. Admin-created users are auto-verified (email_verified_at = now). max_teams is floored by the user's owned-team count in UpdateUserRequest::after().
