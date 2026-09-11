<?php

use App\Actions\Teams\AddTeamMember;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function teamMemberWithRole(Team $team, string $role): User
{
    $user = User::factory()->create();

    (new AddTeamMember)->handle($team, $user, $role);

    return $user;
}

it('allows a member to view the team', function () {
    $team = Team::factory()->create();
    $user = teamMemberWithRole($team, 'viewer');

    expect($user->can('view', $team))->toBeTrue();
});

it('denies outsiders from viewing the team', function () {
    $team = Team::factory()->create();
    $outsider = User::factory()->create();

    expect($outsider->can('view', $team))->toBeFalse();
});

it('allows the owner to update and delete the team', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create();
    $team->update(['owner_id' => $owner->getKey()]);

    (new AddTeamMember)->handle($team, $owner, 'owner');

    expect($owner->can('update', $team))->toBeTrue()
        ->and($owner->can('delete', $team))->toBeTrue();
});

it('allows the admin role to perform admin abilities', function (string $ability) {
    $team = Team::factory()->create();
    $user = teamMemberWithRole($team, 'admin');

    expect($user->can($ability, $team))->toBeTrue();
})->with(['update', 'manageMembers', 'assignRoles']);

it('denies the editor role from admin abilities', function (string $ability) {
    $team = Team::factory()->create();
    $user = teamMemberWithRole($team, 'editor');

    expect($user->can($ability, $team))->toBeFalse();
})->with(['update', 'delete', 'manageMembers', 'assignRoles']);

it('denies the admin role from deleting the team', function () {
    $team = Team::factory()->create();
    $user = teamMemberWithRole($team, 'admin');

    expect($user->can('delete', $team))->toBeFalse();
});

it('grants a super admin every team ability without membership', function () {
    $team = Team::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    expect($superAdmin->can('view', $team))->toBeTrue()
        ->and($superAdmin->can('update', $team))->toBeTrue()
        ->and($superAdmin->can('delete', $team))->toBeTrue()
        ->and($superAdmin->can('manageMembers', $team))->toBeTrue();
});
