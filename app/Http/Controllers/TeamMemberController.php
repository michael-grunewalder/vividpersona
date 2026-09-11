<?php

namespace App\Http\Controllers;

use App\Actions\Teams\ChangeTeamMemberRole;
use App\Actions\Teams\RemoveTeamMember;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamMemberController extends Controller
{
    /**
     * Change a member's role within a team.
     */
    public function update(Request $request, Team $team, User $member): RedirectResponse
    {
        $this->authorize('assignRoles', $team);

        if ($team->owner_id === $member->getKey()) {
            return back()->withErrors(['role' => __('teams.owner_guard')]);
        }

        $role = $request->validate([
            'role' => ['required', Rule::enum(TeamRole::class)],
        ])['role'];

        (new ChangeTeamMemberRole)->handle($team, $member, $role);

        return back()->with('success', __('teams.member_role_updated'));
    }

    /**
     * Remove a member from a team.
     */
    public function destroy(Request $request, Team $team, User $member): RedirectResponse
    {
        $this->authorize('manageMembers', $team);

        if ($team->owner_id === $member->getKey()) {
            return back()->withErrors(['role' => __('teams.owner_guard')]);
        }

        (new RemoveTeamMember)->handle($team, $member);

        return back()->with('success', __('teams.member_removed'));
    }
}
