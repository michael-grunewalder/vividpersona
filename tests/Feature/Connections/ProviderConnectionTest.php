<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiProviderType;
use App\Models\ApiProvider;
use App\Models\ProviderConnection;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;

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

it('lists providers with their connection status', function () {
    $user = User::factory()->create();
    $team = teamFor($user);
    $llm = ApiProvider::factory()->create(['machine_name' => 'claude', 'type' => ApiProviderType::Llm]);
    $media = ApiProvider::factory()->create([
        'machine_name' => 'fal',
        'type' => ApiProviderType::Media,
        'meta' => [['name' => 'api_key', 'description' => 'FAL key']],
    ]);
    ProviderConnection::factory()->create([
        'team_id' => $team->getKey(),
        'provider_id' => $llm->getKey(),
        'credentials' => ['api_key' => 'secret'],
    ]);

    $this->actingAs($user)
        ->get('/connections')
        ->assertOk()
        ->assertSee('claude')
        ->assertSee('fal')
        ->assertSee('Connected');
});

it('connects a provider and stores credentials encrypted', function () {
    $user = User::factory()->create();
    $team = teamFor($user);
    $provider = ApiProvider::factory()->create([
        'machine_name' => 'fal',
        'type' => ApiProviderType::Media,
        'meta' => [['name' => 'api_key', 'description' => 'FAL key']],
    ]);

    $this->actingAs($user)
        ->post(route('connections.store', $provider), [
            'credentials' => ['api_key' => 'fal-secret-key'],
        ])
        ->assertRedirect(route('connections.index'));

    $connection = ProviderConnection::query()->firstOrFail();

    expect($connection->team_id)->toBe($team->getKey())
        ->and($connection->credential('api_key'))->toBe('fal-secret-key');

    expect(DB::table('provider_connections')->where('id', $connection->getKey())->value('credentials'))
        ->not->toContain('fal-secret-key');

    expect($team->providerCredentials($provider))->toBe(['api_key' => 'fal-secret-key']);
});

it('requires all provider credential fields', function () {
    $user = User::factory()->create();
    teamFor($user);
    $provider = ApiProvider::factory()->create([
        'machine_name' => 'fal',
        'type' => ApiProviderType::Media,
        'meta' => [
            ['name' => 'api_key', 'description' => 'FAL key'],
            ['name' => 'secret', 'description' => 'Secret'],
        ],
    ]);

    $this->actingAs($user)
        ->post(route('connections.store', $provider), [
            'credentials' => ['api_key' => 'x'],
        ])
        ->assertSessionHasErrors('credentials.secret');
});

it('saves connections under the current team only', function () {
    $user = User::factory()->create(['meta' => ['max_teams' => 3]]);
    $teamA = (new CreateTeam)->handle($user, 'Alpha', 'owner');
    $teamB = (new CreateTeam)->handle($user, 'Beta', 'owner');
    $user->setCurrentTeam($teamA);
    $provider = ApiProvider::factory()->create([
        'machine_name' => 'fal',
        'type' => ApiProviderType::Media,
        'meta' => [['name' => 'api_key', 'description' => 'FAL key']],
    ]);

    $this->actingAs($user)
        ->post(route('connections.store', $provider), [
            'credentials' => ['api_key' => 'x'],
        ])
        ->assertRedirect();

    expect($provider->connectionFor($teamA))->not->toBeNull()
        ->and($provider->connectionFor($teamB))->toBeNull();
});
