<?php

use App\Actions\Teams\AddTeamMember;
use App\Actions\Teams\CreateTeam;
use App\Enums\TeamInvitationStatus;
use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('lets an admin invite a member by name and email', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');

    $this->actingAs($owner)
        ->post("/teams/{$team->slug}/invitations", [
            'name' => 'Jane Newcomer',
            'email' => 'new@example.com',
            'role' => 'editor',
        ])
        ->assertSessionHas('success');

    $invitation = TeamInvitation::query()->firstOrFail();

    expect($invitation->team_id)->toBe($team->getKey())
        ->and($invitation->name)->toBe('Jane Newcomer')
        ->and($invitation->email)->toBe('new@example.com')
        ->and($invitation->role)->toBe(TeamRole::Editor)
        ->and($invitation->status)->toBe(TeamInvitationStatus::Pending)
        ->and($invitation->token)->not->toBeNull();
});

it('requires a name when inviting a member', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');

    $this->actingAs($owner)
        ->post("/teams/{$team->slug}/invitations", [
            'email' => 'new@example.com',
            'role' => 'editor',
        ])
        ->assertSessionHasErrors('name');
});

it('sends an invitation email to the invited address', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');

    $this->actingAs($owner)
        ->post("/teams/{$team->slug}/invitations", [
            'name' => 'Jane Newcomer',
            'email' => 'new@example.com',
            'role' => 'editor',
        ])
        ->assertSessionHas('success');

    Notification::assertSentOnDemand(
        TeamInvitationNotification::class,
        fn (TeamInvitationNotification $notification, array $channels, object $notifiable) => $notifiable->routes['mail'] === 'new@example.com',
    );
});

it('renders the invitation email with the team name', function () {
    $owner = User::factory()->create(['name' => 'Jane Owner']);
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->getKey(),
        'name' => 'Jane Newcomer',
        'email' => 'new@example.com',
        'role' => TeamRole::Editor,
        'created_by' => $owner->getKey(),
    ]);

    $mail = (new TeamInvitationNotification($invitation))->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toContain('Acme')
        ->and(implode(' ', $mail->introLines))->toContain('Acme')
        ->and(implode(' ', $mail->introLines))->toContain('Jane Owner');
});

it('rejects inviting an existing member', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $member = User::factory()->create(['email' => 'member@example.com']);
    (new AddTeamMember)->handle($team, $member, 'viewer');

    $this->actingAs($owner)
        ->post("/teams/{$team->slug}/invitations", [
            'name' => 'Member',
            'email' => 'member@example.com',
            'role' => 'editor',
        ])
        ->assertSessionHasErrors('email');
});

it('rejects a duplicate pending invitation', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    TeamInvitation::factory()->create(['team_id' => $team->getKey(), 'email' => 'new@example.com']);

    $this->actingAs($owner)
        ->post("/teams/{$team->slug}/invitations", [
            'name' => 'Jane Newcomer',
            'email' => 'new@example.com',
            'role' => 'editor',
        ])
        ->assertSessionHasErrors('email');
});

it('forbids an editor from inviting members', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $editor = User::factory()->create();
    (new AddTeamMember)->handle($team, $editor, 'editor');

    $this->actingAs($editor)
        ->post("/teams/{$team->slug}/invitations", [
            'name' => 'Jane Newcomer',
            'email' => 'new@example.com',
            'role' => 'editor',
        ])
        ->assertForbidden();
});

it('pre-fills the registration form from the invitation', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->getKey(),
        'name' => 'Jane Newcomer',
        'email' => 'new@example.com',
    ]);

    $this->get(route('register', ['invitation' => $invitation->token]))
        ->assertOk()
        ->assertSee('Jane Newcomer')
        ->assertSee('new@example.com')
        ->assertSee('Acme');
});

it('lets an invited user accept the invitation', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $invited = User::factory()->create(['email' => 'new@example.com']);
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->getKey(),
        'email' => 'new@example.com',
        'role' => TeamRole::Editor,
    ]);

    $this->actingAs($invited)
        ->post(route('teams.invitations.accept', $invitation))
        ->assertRedirect(route('teams.index'));

    expect($invited->belongsToTeam($team))->toBeTrue()
        ->and($invited->hasRoleInTeam($team, 'editor'))->toBeTrue()
        ->and($invitation->fresh()->status)->toBe(TeamInvitationStatus::Accepted);
});

it('prevents accepting an invitation meant for someone else', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $other = User::factory()->create(['email' => 'other@example.com']);
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->getKey(),
        'email' => 'new@example.com',
    ]);

    $this->actingAs($other)
        ->post(route('teams.invitations.accept', $invitation))
        ->assertForbidden();

    expect($invitation->fresh()->status)->toBe(TeamInvitationStatus::Pending);
});

it('lets an invited user decline the invitation', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $invited = User::factory()->create(['email' => 'new@example.com']);
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->getKey(),
        'email' => 'new@example.com',
    ]);

    $this->actingAs($invited)
        ->post(route('teams.invitations.decline', $invitation))
        ->assertRedirect(route('teams.index'));

    expect($invitation->fresh()->status)->toBe(TeamInvitationStatus::Declined);
});

it('automatically accepts pending invitations when the invited email registers', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->getKey(),
        'email' => 'new@example.com',
        'role' => TeamRole::Viewer,
    ]);

    $this->post('/register', [
        'name' => 'New Member',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'new@example.com')->firstOrFail();

    expect($user->belongsToTeam($team))->toBeTrue()
        ->and($user->hasRoleInTeam($team, 'viewer'))->toBeTrue()
        ->and($invitation->fresh()->status)->toBe(TeamInvitationStatus::Accepted);
});
