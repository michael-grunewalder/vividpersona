<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiService;
use App\Models\Team;
use App\Models\TeamApiCredential;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function teamFor(User $user): Team
{
    return (new CreateTeam)->handle($user, 'Acme', 'owner');
}

it('redirects guests to the login page', function () {
    $this->get('/connections')
        ->assertRedirect(route('login'));
});

it('lists the services with their connection status', function () {
    $user = User::factory()->create();
    $team = teamFor($user);
    TeamApiCredential::factory()->forService(ApiService::Claude)->create([
        'team_id' => $team->getKey(),
        'credentials' => ['api_key' => 'secret'],
    ]);

    $this->actingAs($user)
        ->get('/connections')
        ->assertOk()
        ->assertSee('Claude')
        ->assertSee('FAL.AI')
        ->assertSee('Connected');
});

it('connects a service and stores credentials encrypted', function () {
    Http::fake(['https://fal.run/users/me' => Http::response([], 200)]);

    $user = User::factory()->create();
    $team = teamFor($user);

    $this->actingAs($user)
        ->post(route('connections.store', ApiService::Fal->value), [
            'fal' => ['api_key' => 'fal-secret-key'],
        ])
        ->assertRedirect(route('connections.index'));

    $connection = TeamApiCredential::query()->firstOrFail();

    expect($connection->team_id)->toBe($team->getKey())
        ->and($connection->service)->toBe(ApiService::Fal)
        ->and($connection->apiKey())->toBe('fal-secret-key');

    expect(DB::table('team_api_credentials')->where('id', $connection->getKey())->value('credentials'))
        ->not->toContain('fal-secret-key');

    expect($team->credentialsFor(ApiService::Fal))->toBe(['api_key' => 'fal-secret-key']);
});

it('requires the api key field', function () {
    $user = User::factory()->create();
    teamFor($user);

    $this->actingAs($user)
        ->post(route('connections.store', ApiService::Fal->value), [
            'fal' => ['api_key' => ''],
        ])
        ->assertSessionHasErrors('fal.api_key');
});

it('rejects an invalid api key', function () {
    Http::fake(['https://fal.run/users/me' => Http::response([], 401)]);

    $user = User::factory()->create();
    teamFor($user);

    $this->actingAs($user)
        ->post(route('connections.store', ApiService::Fal->value), [
            'fal' => ['api_key' => 'bad-key'],
        ])
        ->assertSessionHasErrors('fal.api_key');

    expect(TeamApiCredential::query()->count())->toBe(0);
});

it('reconnects without touching other stored credentials', function () {
    Http::fake(['https://fal.run/users/me' => Http::response([], 200)]);

    $user = User::factory()->create();
    $team = teamFor($user);
    TeamApiCredential::factory()->create([
        'team_id' => $team->getKey(),
        'service' => ApiService::Fal,
        'credentials' => ['api_key' => 'old-key', 'secret' => 'keep-me'],
    ]);

    $this->actingAs($user)
        ->post(route('connections.store', ApiService::Fal->value), [
            'fal' => ['api_key' => 'new-key'],
        ])
        ->assertRedirect(route('connections.index'));

    expect($team->fresh()->credentialsFor(ApiService::Fal))
        ->toBe(['api_key' => 'new-key', 'secret' => 'keep-me']);
});

it('disconnects a service and removes its credentials', function () {
    $user = User::factory()->create();
    $team = teamFor($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $user->setCurrentTeam($team);

    $this->actingAs($user)
        ->delete(route('connections.disconnect', ApiService::Fal->value))
        ->assertRedirect(route('connections.index'));

    expect($team->hasCredential(ApiService::Fal))->toBeFalse();
});

it('saves connections under the current team only', function () {
    Http::fake(['https://fal.run/users/me' => Http::response([], 200)]);

    $user = User::factory()->create(['meta' => ['max_teams' => 3]]);
    $teamA = (new CreateTeam)->handle($user, 'Alpha', 'owner');
    $teamB = (new CreateTeam)->handle($user, 'Beta', 'owner');
    $user->setCurrentTeam($teamA);

    $this->actingAs($user)
        ->post(route('connections.store', ApiService::Fal->value), [
            'fal' => ['api_key' => 'x'],
        ])
        ->assertRedirect();

    expect($teamA->hasCredential(ApiService::Fal))->toBeTrue()
        ->and($teamB->hasCredential(ApiService::Fal))->toBeFalse();
});

it('sets and persists the team default llm', function () {
    $user = User::factory()->create();
    $team = teamFor($user);
    TeamApiCredential::factory()->forService(ApiService::Claude)->create(['team_id' => $team->getKey()]);
    TeamApiCredential::factory()->forService(ApiService::DeepSeek)->create(['team_id' => $team->getKey()]);
    $user->setCurrentTeam($team);

    $this->actingAs($user)
        ->post(route('connections.default-llm'), ['default_llm' => ApiService::DeepSeek->value])
        ->assertRedirect(route('connections.index'));

    expect($team->fresh()->default_llm)->toBe(ApiService::DeepSeek->value)
        ->and($team->fresh()->defaultLlmService())->toBe(ApiService::DeepSeek);
});

it('defaults to the first connected llm when none is configured', function () {
    $user = User::factory()->create();
    $team = teamFor($user);
    TeamApiCredential::factory()->forService(ApiService::DeepSeek)->create(['team_id' => $team->getKey()]);

    expect($team->defaultLlmService())->toBe(ApiService::DeepSeek);
});
