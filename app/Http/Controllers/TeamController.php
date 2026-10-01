<?php

namespace App\Http\Controllers;

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiService;
use App\Enums\TeamInvitationStatus;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'mediaServices' => collect(ApiService::media())
                ->filter(fn (ApiService $service) => $team->hasCredential($service))
                ->values(),
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

    /**
     * Set the team's storage quota (in GB). Empty means unlimited.
     */
    public function updateStorage(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'storage_limit_gb' => ['nullable', 'numeric', 'min:0', 'max:1048576'],
        ]);

        $limit = filled($validated['storage_limit_gb'] ?? null)
            ? (int) round((float) $validated['storage_limit_gb'] * 1024 * 1024 * 1024)
            : null;

        $team->update(['storage_limit_bytes' => $limit]);

        return redirect()->route('teams.show', $team)->with('success', __('teams.storage_updated'));
    }

    /**
     * Set the team's default media (image) provider.
     */
    public function updateProvider(Request $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'default_media_provider' => ['nullable', Rule::in(ApiService::mediaValues())],
        ]);

        $team->update(['default_media_provider' => $validated['default_media_provider'] ?? null]);

        return redirect()->route('teams.show', $team)->with('success', __('teams.provider_updated'));
    }
}
