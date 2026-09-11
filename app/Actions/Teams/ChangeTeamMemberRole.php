<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use App\Support\TeamContext;
use InvalidArgumentException;

class ChangeTeamMemberRole
{
    /**
     * Replace a team member's role within the given team.
     */
    public function handle(Team $team, User $member, string $role): void
    {
        if ($team->owner_id === $member->getKey() && $role !== 'owner') {
            throw new InvalidArgumentException('The team owner role cannot be changed.');
        }

        TeamContext::run($team, function () use ($member, $role): void {
            $member->unsetRelation('roles')->unsetRelation('permissions');

            $member->syncRoles([$role]);
        });
    }
}
