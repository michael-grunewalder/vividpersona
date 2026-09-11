<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use App\Support\TeamContext;
use InvalidArgumentException;

class RemoveTeamMember
{
    /**
     * Remove a user from a team and drop their roles within that team.
     */
    public function handle(Team $team, User $member): void
    {
        if ($team->owner_id === $member->getKey()) {
            throw new InvalidArgumentException('The team owner cannot be removed from the team.');
        }

        TeamContext::run($team, function () use ($member): void {
            $member->unsetRelation('roles')->unsetRelation('permissions');

            $member->syncRoles([]);
        });

        $team->members()->detach($member->getKey());
    }
}
