<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTeamContext
{
    /**
     * Resolve the team for the current route and make it the active
     * permissions team, so role/permission checks are team-scoped.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $team = $this->resolveTeam($request);

        if ($user = $request->user()) {
            $team ??= $user->currentTeam();

            $user->unsetRelation('roles')->unsetRelation('permissions');
        }

        setPermissionsTeamId($team?->getKey());

        return $next($request);
    }

    protected function resolveTeam(Request $request): ?Team
    {
        $parameter = $request->route()?->parameter('team');

        if ($parameter instanceof Team) {
            return $parameter;
        }

        if (! is_string($parameter) || $parameter === '') {
            return null;
        }

        $team = Team::query()
            ->where((new Team)->getRouteKeyName(), $parameter)
            ->firstOrFail();

        // Store the resolved model so SubstituteBindings does not query again.
        $request->route()->setParameter('team', $team);

        return $team;
    }
}
