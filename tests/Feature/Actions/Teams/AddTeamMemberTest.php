<?php

use App\Actions\Teams\AddTeamMember;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('adds a user to a team and assigns the role in that team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    (new AddTeamMember)->handle($team, $user, 'editor');

    expect($user->belongsToTeam($team))->toBeTrue()
        ->and($user->hasRoleInTeam($team, 'editor'))->toBeTrue();
});

it('does not grant the role or membership in other teams', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $user = User::factory()->create();

    (new AddTeamMember)->handle($teamA, $user, 'editor');

    expect($user->belongsToTeam($teamB))->toBeFalse()
        ->and($user->hasRoleInTeam($teamB, 'editor'))->toBeFalse();
});
