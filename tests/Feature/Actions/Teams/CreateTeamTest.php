<?php

use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a team owned by the user with an admin role', function () {
    $user = User::factory()->create();

    $team = (new CreateTeam)->handle($user, 'Personal');

    expect($team->owner_id)->toBe($user->getKey())
        ->and($team->name)->toBe('Personal')
        ->and($user->belongsToTeam($team))->toBeTrue()
        ->and($user->hasRoleInTeam($team, 'admin'))->toBeTrue();
});

it('generates unique slugs for teams with the same name', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $teamA = (new CreateTeam)->handle($userA, 'Personal');
    $teamB = (new CreateTeam)->handle($userB, 'Personal');

    expect($teamA->slug)->not->toBe($teamB->slug)
        ->and(Team::query()->where('slug', $teamA->slug)->count())->toBe(1);
});
