---
paths:
  - 'app/**'
---

# App

## Team-scoped permission checks need explicit team context
Spatie's teams mode scopes role/permission checks to the active team set via setPermissionsTeamId(). HTTP requests get the context from the SetTeamContext middleware (before SubstituteBindings). Jobs and actions must wrap work in TeamContext::run($team, ...) or use User::hasRoleInTeam()/hasPermissionInTeam(); those unset cached roles/permissions relations first. Never rely on ambient team state in queued work.
