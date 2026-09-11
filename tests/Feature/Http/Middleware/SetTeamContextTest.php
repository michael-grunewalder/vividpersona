<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'team.context'])->get('/teams/{team}/team-context', function (Team $team) {
        return response()->json(['team_id' => getPermissionsTeamId()]);
    });
});

it('sets the active permissions team from the route', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $team->members()->attach($user);

    $this->actingAs($user)
        ->getJson("/teams/{$team->slug}/team-context")
        ->assertOk()
        ->assertJson(['team_id' => $team->getKey()]);
});

it('returns 404 when the team slug does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/teams/does-not-exist/team-context')
        ->assertNotFound();
});
