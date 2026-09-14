<?php

use App\Actions\Teams\CreateTeam;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Route::middleware(['web', 'auth', 'team.context'])->get('/team-context-echo', function () {
        return response()->json(['team_id' => getPermissionsTeamId()]);
    });
});

it('switches the active team', function () {
    $user = User::factory()->create();
    $teamA = (new CreateTeam)->handle($user, 'Alpha', 'owner');
    $teamB = (new CreateTeam)->handle($user, 'Beta', 'owner');

    $this->actingAs($user)
        ->post('/current-team', ['team_id' => $teamB->getKey()])
        ->assertRedirect();

    expect(session('current_team_id'))->toBe($teamB->getKey());

    $this->actingAs($user)->getJson('/team-context-echo')
        ->assertOk()
        ->assertJson(['team_id' => $teamB->getKey()]);
});

it('defaults the active team to the first team', function () {
    $user = User::factory()->create();
    $teamA = (new CreateTeam)->handle($user, 'Alpha', 'owner');
    (new CreateTeam)->handle($user, 'Beta', 'owner');

    expect($user->fresh()->currentTeam()->getKey())->toBe($teamA->getKey())
        ->and(session('current_team_id'))->toBe($teamA->getKey());
});

it('applies the default active team as the permissions context', function () {
    $user = User::factory()->create();
    $teamA = (new CreateTeam)->handle($user, 'Alpha', 'owner');

    $this->actingAs($user)->getJson('/team-context-echo')
        ->assertOk()
        ->assertJson(['team_id' => $teamA->getKey()]);
});

it('returns null when the user has no teams', function () {
    $user = User::factory()->create();

    expect($user->currentTeam())->toBeNull();
});

it('forbids switching to a team the user does not belong to', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    (new CreateTeam)->handle($other, 'Other Team', 'owner');

    $this->actingAs($user)
        ->post('/current-team', ['team_id' => $other->ownedTeams->first()->getKey()])
        ->assertNotFound();
});

it('renders the team switcher with the current team in the sidebar', function () {
    $user = User::factory()->create();
    (new CreateTeam)->handle($user, 'Alpha', 'owner');

    $html = $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->getContent();

    expect($html)->toContain('Alpha')
        ->and($html)->toContain('current-team');
});
