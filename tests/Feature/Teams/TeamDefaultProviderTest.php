<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiService;
use App\Models\TeamApiCredential;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function teamWithUser(): array
{
    $user = User::factory()->create(['meta' => ['max_teams' => 3]]);
    $team = (new CreateTeam)->handle($user, 'Acme', 'owner');
    $user->setCurrentTeam($team);

    return [$user, $team];
}

it('returns null when no media provider is connected', function () {
    [$user, $team] = teamWithUser();

    expect($team->defaultMediaService())->toBeNull();
});

it('falls back to the first connected media provider', function () {
    [$user, $team] = teamWithUser();
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);

    expect($team->defaultMediaService())->toBe(ApiService::Fal);
});

it('uses the configured provider when connected', function () {
    [$user, $team] = teamWithUser();
    TeamApiCredential::factory()->forService(ApiService::WaveSpeed)->create(['team_id' => $team->getKey()]);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);

    $team->update(['default_media_provider' => ApiService::WaveSpeed->value]);

    expect($team->defaultMediaService())->toBe(ApiService::WaveSpeed);
});

it('ignores a configured provider that is not connected', function () {
    [$user, $team] = teamWithUser();
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);

    $team->update(['default_media_provider' => ApiService::WaveSpeed->value]);

    expect($team->defaultMediaService())->toBe(ApiService::Fal);
});

it('updates the default media provider from the team settings', function () {
    [$user, $team] = teamWithUser();
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);

    $this->actingAs($user)
        ->patch(route('teams.provider.update', $team), ['default_media_provider' => 'fal'])
        ->assertRedirect(route('teams.show', $team));

    expect($team->fresh()->default_media_provider)->toBe('fal');
});

it('rejects a non-media provider', function () {
    [$user, $team] = teamWithUser();

    $this->actingAs($user)
        ->patch(route('teams.provider.update', $team), ['default_media_provider' => 'claude'])
        ->assertSessionHasErrors('default_media_provider');
});
