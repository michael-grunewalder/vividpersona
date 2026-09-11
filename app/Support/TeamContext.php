<?php

namespace App\Support;

use App\Models\Team;
use Closure;

class TeamContext
{
    /**
     * Run a callback with the given team as the active permissions team,
     * restoring the previous context afterwards.
     *
     * Use this around queued work (e.g. AI generation jobs) so role and
     * permission checks never depend on ambient request state.
     */
    public static function run(Team|string|null $team, Closure $callback): mixed
    {
        $previous = getPermissionsTeamId();

        setPermissionsTeamId($team instanceof Team ? $team->getKey() : $team);

        try {
            return $callback();
        } finally {
            setPermissionsTeamId($previous);
        }
    }
}
