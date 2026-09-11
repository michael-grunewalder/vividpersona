<?php

namespace App\Http\Controllers;

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamInvitationStatus;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * List the current user's teams and pending invitations.
     */
    public function index(): View
    {
        $user = auth()->user();

        $teams = $user->teams()
            ->with('owner')
            ->withCount('members')
            ->get()
            ->map(fn (Team $team) => [
                'team' => $team,
                'role' => $user->roleInTeam($team),
            ]);

        $invitations = TeamInvitation::query()
            ->where('email', $user->email)
            ->where('status', TeamInvitationStatus::Pending)
            ->with('team')
            ->get();

        return view('teams.index', [
            'teams' => $teams,
            'invitations' => $invitations,
        ]);
    }

    /**
     * Show the form to create a new team.
     */
    public function create(): View
    {
        return view('teams.create');
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        if ($user->ownedTeams()->count() >= $user->meta->maxTeams) {
            return back()->withErrors(['name' => __('teams.max_teams_reached', ['count' => $user->meta->maxTeams])]);
        }

        $team = (new CreateTeam)->handle($user, $request->name, 'owner');

        return redirect()->route('teams.show', $team)->with('success', __('teams.created'));
    }

    /**
     * Show a team's settings and members.
     */
    public function show(Team $team): View
    {
        $this->authorize('view', $team);

        $members = $team->members()
            ->orderBy('name')
            ->get()
            ->map(fn ($member) => [
                'user' => $member,
                'role' => $member->roleInTeam($team),
            ]);

        return view('teams.show', [
            'team' => $team,
            'members' => $members,
            'roles' => TeamRole::cases(),
        ]);
    }

    /**
     * Rename an existing team.
     */
    public function update(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team->update(['name' => $request->name]);

        return redirect()->route('teams.show', $team)->with('success', __('teams.renamed'));
    }
}
