<?php

use App\Actions\Teams\AddTeamMember;
use App\Actions\Teams\RemoveTeamMember;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use InvalidArgumentException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('removes a member and drops their roles in the team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    (new AddTeamMember)->handle($team, $user, 'editor');
    (new RemoveTeamMember)->handle($team, $user);

    expect($user->belongsToTeam($team))->toBeFalse()
        ->and($user->hasRoleInTeam($team, 'editor'))->toBeFalse();
});

it('rejects removing the team owner', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create();
    $team->update(['owner_id' => $owner->getKey()]);

    expect(fn () => (new RemoveTeamMember)->handle($team, $owner))
        ->toThrow(InvalidArgumentException::class);
});
