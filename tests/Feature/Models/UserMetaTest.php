<?php

use App\Enums\Plan;
use App\Models\User;
use App\ValueObjects\UserSettings;

test('new users default to the free plan and one team', function () {
    $user = User::factory()->create();

    expect($user->meta)->toBeInstanceOf(UserSettings::class)
        ->and($user->meta->currentPlan)->toBe(Plan::Free)
        ->and($user->meta->maxTeams)->toBe(1);
});

test('assigning an array keeps only the known settings fields', function () {
    $user = User::factory()->create();

    $user->meta = ['current_plan' => 'free', 'max_teams' => 3, 'unknown' => 'ignored'];
    $user->save();

    $meta = $user->fresh()->meta;

    expect($meta->maxTeams)->toBe(3)
        ->and($meta->toArray())->toBe(['current_plan' => 'free', 'max_teams' => 3]);
});

test('accepts a UserSettings instance for assignment', function () {
    $user = User::factory()->create();

    $user->meta = new UserSettings(maxTeams: 5);
    $user->save();

    expect($user->fresh()->meta->maxTeams)->toBe(5);
});

test('mutating the settings object persists on save', function () {
    $user = User::factory()->create();

    $user->meta->maxTeams = 8;
    $user->save();

    expect($user->fresh()->meta->maxTeams)->toBe(8);
});

test('serializes only the known settings fields', function () {
    $user = User::factory()->create(['meta' => ['max_teams' => 4]]);

    expect($user->toArray()['meta'])->toBe(['current_plan' => 'free', 'max_teams' => 4]);
});

test('exposes the current plan as a Plan enum', function () {
    $user = User::factory()->create(['meta' => ['current_plan' => 'free']]);

    expect($user->meta->currentPlan)->toBe(Plan::Free);
});
