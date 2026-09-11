---
paths:
  - 'app/Models/**'
---

# Models

## Roles are global; assignment is team-scoped
Roles are global: RolePermissionSeeder creates each role once with team_id null, so a role has identical permissions in every team. Per-team differences exist only in assignment (model_has_roles.team_id), never in the role's permission set. Add or rename roles/permissions only in RolePermissionSeeder, never per-team.
