<?php

namespace App\Http\Controllers;

use App\Actions\Teams\AddTeamMember;
use App\Enums\TeamInvitationStatus;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class TeamInvitationController extends Controller
{
    /**
     * Invite a user to a team by email.
     */
    public function store(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('manageMembers', $team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', Rule::enum(TeamRole::class)],
        ]);

        if ($team->members()->where('users.email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => __('invitations.already_member')]);
        }

        $alreadyInvited = TeamInvitation::query()
            ->where('team_id', $team->getKey())
            ->where('email', $validated['email'])
            ->where('status', TeamInvitationStatus::Pending)
            ->exists();

        if ($alreadyInvited) {
            return back()->withErrors(['email' => __('invitations.already_invited')]);
        }

        $invitation = TeamInvitation::query()->create([
            'team_id' => $team->getKey(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'created_by' => $request->user()->getKey(),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        return back()->with('success', __('invitations.sent'));
    }

    /**
     * Accept a pending invitation.
     */
    public function accept(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->email === $request->user()->email, 403);
        abort_unless($invitation->status === TeamInvitationStatus::Pending, 403);

        (new AddTeamMember)->handle($invitation->team, $request->user(), $invitation->role->value);

        $invitation->update(['status' => TeamInvitationStatus::Accepted]);

        return redirect()->route('teams.index')->with('success', __('invitations.accepted'));
    }

    /**
     * Decline a pending invitation.
     */
    public function decline(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->email === $request->user()->email, 403);
        abort_unless($invitation->status === TeamInvitationStatus::Pending, 403);

        $invitation->update(['status' => TeamInvitationStatus::Declined]);

        return redirect()->route('teams.index')->with('success', __('invitations.declined'));
    }
}
