<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->hasPermissionInTeam($team, 'team.update');
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->hasPermissionInTeam($team, 'team.delete');
    }

    /**
     * Determine whether the user can manage the team's members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        return $user->hasPermissionInTeam($team, 'team.manage-members');
    }

    /**
     * Determine whether the user can assign roles to the team's members.
     */
    public function assignRoles(User $user, Team $team): bool
    {
        return $user->hasPermissionInTeam($team, 'team.assign-roles');
    }
}
