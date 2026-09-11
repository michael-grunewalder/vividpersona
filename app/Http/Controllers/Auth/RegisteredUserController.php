<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Teams\AddTeamMember;
use App\Actions\Teams\CreateTeam;
use App\Enums\TeamInvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(Request $request): View
    {
        $invitation = null;

        if ($token = $request->string('invitation')->toString()) {
            $invitation = TeamInvitation::query()
                ->where('token', $token)
                ->where('status', TeamInvitationStatus::Pending)
                ->first();
        }

        return view('auth.register', ['invitation' => $invitation]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create($validated);

        (new CreateTeam)->handle($user, __('auth.personal_team_name'));

        $this->acceptPendingInvitations($user);

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    /**
     * Accept any pending team invitations for the newly registered email.
     */
    private function acceptPendingInvitations(User $user): void
    {
        TeamInvitation::query()
            ->where('email', $user->email)
            ->where('status', TeamInvitationStatus::Pending)
            ->get()
            ->each(function (TeamInvitation $invitation) use ($user): void {
                (new AddTeamMember)->handle($invitation->team, $user, $invitation->role->value);

                $invitation->update(['status' => TeamInvitationStatus::Accepted]);
            });
    }
}
