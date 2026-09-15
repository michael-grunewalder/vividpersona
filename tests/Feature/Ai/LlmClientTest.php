<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiService;
use App\Models\Team;
use App\Models\TeamApiCredential;
use App\Models\User;
use App\Services\Ai\BackstoryAnalyst;
use App\Services\Ai\Clients\ChatGptClient;
use App\Services\Ai\Clients\ClaudeClient;
use App\Services\Ai\Clients\DeepSeekClient;
use App\Services\Ai\LlmClientFactory;
use App\Services\Ai\PromptEnhancer;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Enums\Lab;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function llmTeam(User $user): Team
{
    $team = (new CreateTeam)->handle($user, 'Acme', 'owner');
    $user->setCurrentTeam($team);

    return $team;
}

it('builds the right client per service', function () {
    expect(LlmClientFactory::make(ApiService::Claude, 'k'))->toBeInstanceOf(ClaudeClient::class)
        ->and(LlmClientFactory::make(ApiService::ChatGpt, 'k'))->toBeInstanceOf(ChatGptClient::class)
        ->and(LlmClientFactory::make(ApiService::DeepSeek, 'k'))->toBeInstanceOf(DeepSeekClient::class);
});

it('maps each client to its lab and model', function () {
    expect(LlmClientFactory::make(ApiService::Claude, 'k')->lab())->toBe(Lab::Anthropic)
        ->and(LlmClientFactory::make(ApiService::ChatGpt, 'k')->lab())->toBe(Lab::OpenAI)
        ->and(LlmClientFactory::make(ApiService::DeepSeek, 'k')->lab())->toBe(Lab::DeepSeek)
        ->and(LlmClientFactory::make(ApiService::Claude, 'k')->model())->not->toBe('');
});

it('injects the team key into the ai config', function () {
    $client = LlmClientFactory::make(ApiService::Claude, 'team-key');
    $client->configure();

    expect(config('ai.providers.anthropic.key'))->toBe('team-key');
});

it('resolves the override over the team default', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Claude)->create(['team_id' => $team->getKey()]);
    TeamApiCredential::factory()->forService(ApiService::DeepSeek)->create(['team_id' => $team->getKey()]);
    $team->update(['default_llm' => ApiService::Claude->value]);

    expect(LlmClientFactory::forTeam($team, ApiService::DeepSeek))->toBeInstanceOf(DeepSeekClient::class);
});

it('falls back to the team default then the first connected llm', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);
    TeamApiCredential::factory()->forService(ApiService::DeepSeek)->create(['team_id' => $team->getKey()]);

    expect(LlmClientFactory::forTeam($team))->toBeInstanceOf(DeepSeekClient::class);

    TeamApiCredential::factory()->forService(ApiService::ChatGpt)->create(['team_id' => $team->getKey()]);
    $team->update(['default_llm' => ApiService::ChatGpt->value]);

    expect(LlmClientFactory::forTeam($team))->toBeInstanceOf(ChatGptClient::class);
});

it('returns null when the team has no llm credential', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);

    expect(LlmClientFactory::forTeam($team))->toBeNull();
});

it('analyzes a backstory via the team llm client', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Claude)->create(['team_id' => $team->getKey()]);

    BackstoryAnalyst::fake([['sceneNiche' => 'fashion', 'tags' => ['editorial']]]);

    $result = BackstoryAnalyst::analyze($team, 'She runs a slow-fashion studio.', 'white, blonde hair');

    expect($result)->toBe(['sceneNiche' => 'fashion', 'tags' => ['editorial']]);
});

it('analyzing without an llm connection returns null', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);

    expect(BackstoryAnalyst::analyze($team, 'Backstory.', 'desc'))->toBeNull();
});

it('returns the original prompt when no llm is connected', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);

    expect(PromptEnhancer::enhance($team, 'A portrait.'))->toBe('A portrait.');
});

it('enhances a prompt via the team llm client', function () {
    $user = User::factory()->create();
    $team = llmTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Claude)->create(['team_id' => $team->getKey()]);

    AnonymousAgent::fake(['A refined portrait.']);

    expect(PromptEnhancer::enhance($team, 'A portrait.'))->toBe('A refined portrait.');
});
