<?php

use App\Models\Team;
use App\Models\User;

test('teams use string ulid primary keys', function () {
    $team = Team::factory()->create();

    expect($team->getKeyType())->toBe('string')
        ->and($team->getIncrementing())->toBeFalse()
        ->and(strlen($team->getKey()))->toBe(26);
});

test('teams expose the slug as the route key', function () {
    $team = Team::factory()->create(['slug' => 'acme-inc']);

    expect($team->getRouteKey())->toBe('acme-inc');
});

test('teams know their owner', function () {
    $owner = User::factory()->create();

    $team = Team::factory()->create(['owner_id' => $owner->getKey()]);

    expect($team->owner->is($owner))->toBeTrue();
});
