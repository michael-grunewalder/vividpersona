<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use App\Support\TeamContext;

class AddTeamMember
{
    /**
     * Add a user to a team and assign them the given role within that team.
     */
    public function handle(Team $team, User $member, string $role): void
    {
        $team->members()->syncWithoutDetaching([$member->getKey()]);

        TeamContext::run($team, function () use ($member, $role): void {
            $member->unsetRelation('roles')->unsetRelation('permissions');

            $member->assignRole($role);
        });
    }
}
