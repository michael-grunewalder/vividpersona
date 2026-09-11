<?php

use App\Actions\Teams\AddTeamMember;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('users use string ulid primary keys', function () {
    $user = User::factory()->create();

    expect($user->getKeyType())->toBe('string')
        ->and($user->getIncrementing())->toBeFalse()
        ->and(strlen($user->getKey()))->toBe(26);
});

test('users know which teams they belong to', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $user = User::factory()->create();

    $teamA->members()->attach($user);

    expect($user->teams)->toHaveCount(1)
        ->and($user->teams->pluck('id')->contains($teamA->getKey()))->toBeTrue()
        ->and($user->belongsToTeam($teamA))->toBeTrue()
        ->and($user->belongsToTeam($teamB))->toBeFalse();
});

test('users know which teams they own', function () {
    $user = User::factory()->create();

    Team::factory()->count(2)->create(['owner_id' => $user->getKey()]);
    Team::factory()->create();

    expect($user->ownedTeams)->toHaveCount(2);
});

test('roles and permissions are scoped per team', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $user = User::factory()->create();

    (new AddTeamMember)->handle($teamA, $user, 'admin');

    expect($user->hasRoleInTeam($teamA, 'admin'))->toBeTrue()
        ->and($user->hasRoleInTeam($teamB, 'admin'))->toBeFalse()
        ->and($user->hasPermissionInTeam($teamA, 'team.assign-roles'))->toBeTrue()
        ->and($user->hasPermissionInTeam($teamB, 'team.assign-roles'))->toBeFalse();
});
