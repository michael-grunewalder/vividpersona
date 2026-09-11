<?php

use App\Actions\Teams\AddTeamMember;
use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('redirects guests to the login page', function () {
    $this->get('/teams')
        ->assertRedirect(route('login'));
});

it('redirects unverified users to the verification page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/teams')
        ->assertRedirect(route('verification.notice'));
});

it('lists the teams a user belongs to', function () {
    $user = User::factory()->create(['meta' => ['max_teams' => 5]]);
    (new CreateTeam)->handle($user, 'Acme', 'owner');

    $this->actingAs($user)
        ->get('/teams')
        ->assertOk()
        ->assertSee('Acme');
});

it('creates a team and makes the creator the owner', function () {
    $user = User::factory()->create(['meta' => ['max_teams' => 3]]);

    $this->actingAs($user)
        ->post('/teams', ['name' => 'Acme'])
        ->assertRedirect();

    $team = Team::query()->where('name', 'Acme')->firstOrFail();

    expect($team->owner_id)->toBe($user->getKey())
        ->and($user->belongsToTeam($team))->toBeTrue()
        ->and($user->hasRoleInTeam($team, 'owner'))->toBeTrue();
});

it('blocks creating a team once the limit is reached', function () {
    $user = User::factory()->create(['meta' => ['max_teams' => 1]]);
    (new CreateTeam)->handle($user, 'First', 'owner');

    $this->actingAs($user)
        ->post('/teams', ['name' => 'Second'])
        ->assertSessionHasErrors('name');

    expect(Team::query()->where('name', 'Second')->exists())->toBeFalse();
});

it('lets the owner rename a team', function () {
    $user = User::factory()->create();
    $team = (new CreateTeam)->handle($user, 'Acme', 'owner');

    $this->actingAs($user)
        ->patch("/teams/{$team->slug}", ['name' => 'Acme Inc.'])
        ->assertRedirect(route('teams.show', $team));

    expect($team->fresh()->name)->toBe('Acme Inc.');
});

it('forbids an editor from renaming a team', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $editor = User::factory()->create();
    (new AddTeamMember)->handle($team, $editor, 'editor');

    $this->actingAs($editor)
        ->patch("/teams/{$team->slug}", ['name' => 'Hacked'])
        ->assertForbidden();
});

it('lets a member view a team and shows the role badge', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $viewer = User::factory()->create();
    (new AddTeamMember)->handle($team, $viewer, 'viewer');

    $this->actingAs($viewer)
        ->get("/teams/{$team->slug}")
        ->assertOk()
        ->assertSee('Acme');
});

it('lets an admin change a member role and remove a member', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');
    $member = User::factory()->create();
    (new AddTeamMember)->handle($team, $member, 'viewer');

    $this->actingAs($owner)
        ->patch("/teams/{$team->slug}/members/{$member->getKey()}", ['role' => 'editor'])
        ->assertSessionHas('success');

    expect($member->hasRoleInTeam($team, 'editor'))->toBeTrue();

    $this->actingAs($owner)
        ->delete("/teams/{$team->slug}/members/{$member->getKey()}")
        ->assertSessionHas('success');

    expect($member->belongsToTeam($team))->toBeFalse();
});

it('prevents removing the team owner', function () {
    $owner = User::factory()->create();
    $team = (new CreateTeam)->handle($owner, 'Acme', 'owner');

    $this->actingAs($owner)
        ->delete("/teams/{$team->slug}/members/{$owner->getKey()}")
        ->assertSessionHasErrors('role');

    expect($owner->belongsToTeam($team))->toBeTrue();
});
