<?php

use App\Actions\Teams\AddTeamMember;
use App\Actions\Teams\ChangeTeamMemberRole;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use InvalidArgumentException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('replaces the role within the team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    (new AddTeamMember)->handle($team, $user, 'editor');
    (new ChangeTeamMemberRole)->handle($team, $user, 'admin');

    expect($user->hasRoleInTeam($team, 'admin'))->toBeTrue()
        ->and($user->hasRoleInTeam($team, 'editor'))->toBeFalse();
});

it('rejects demoting the team owner', function () {
    $team = Team::factory()->create();
    $owner = User::factory()->create();
    $team->update(['owner_id' => $owner->getKey()]);

    (new AddTeamMember)->handle($team, $owner, 'owner');

    expect(fn () => (new ChangeTeamMemberRole)->handle($team, $owner, 'viewer'))
        ->toThrow(InvalidArgumentException::class);
});
