---
paths:
  - 'app/Http/Middleware/**'
---

# Middleware

## Active team context is session-backed
The active team is session-backed: `User::currentTeam()` reads `session('current_team_id')`, clears stale values and defaults to the user's first team. `SetTeamContext` falls back to `currentTeam()` when the route has no `{team}` param, so the context applies app-wide (dashboard/profile/teams all use `team.context`). Switching happens via POST /current-team (membership-checked). Always call `currentTeam()`/`setCurrentTeam()`, never touch the session key directly.
