<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiProviderType;
use App\Jobs\GenerateInfluencerSetJob;
use App\Models\ApiProvider;
use App\Models\Influencer;
use App\Models\ProviderConnection;
use App\Models\Team;
use App\Models\User;
use App\Services\Media\MediaService;
use App\Support\Prompt\InfluencerPromptBuilder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function influencerTeam(User $user): Team
{
    $team = (new CreateTeam)->handle($user, 'Acme', 'owner');
    $user->setCurrentTeam($team);

    return $team;
}

it('redirects guests to the login page', function () {
    $this->get('/influencers')->assertRedirect(route('login'));
});

it('lists the current team influencers only', function () {
    $user = User::factory()->create();
    $team = influencerTeam($user);
    Influencer::factory()->create(['team_id' => $team->getKey(), 'name' => 'Kayla']);
    Influencer::factory()->create(['name' => 'Outsider']);

    $this->actingAs($user)
        ->get('/influencers')
        ->assertOk()
        ->assertSee('Kayla')
        ->assertDontSee('Outsider');
});

it('renders the create wizard', function () {
    $user = User::factory()->create();
    influencerTeam($user);

    $this->actingAs($user)
        ->get('/influencers/create')
        ->assertOk()
        ->assertSee('Basics');
});

it('creates an influencer for the current team and dispatches generation', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = influencerTeam($user);
    ApiProvider::factory()->create([
        'machine_name' => 'fal',
        'type' => ApiProviderType::Media,
        'meta' => [['name' => 'api_key', 'description' => 'key']],
    ]);

    $this->actingAs($user)->post('/influencers', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 85,
        'vibe_words' => 'Streetwear',
        'niches' => ['Fashion'],
        'aspect_ratio' => '9:16',
        'provider' => 'fal',
        'model' => 'fal-ai/nano-banana-2',
        'ethnicity' => 'White',
        'skin_tone' => 'medium',
        'hair_color' => 'blonde',
        'hair_length' => 'Long',
        'hair_texture' => 'Wavy',
        'eye_color' => 'blue',
        'build' => 'Athletic',
    ])->assertRedirect();

    $influencer = Influencer::query()->firstOrFail();

    expect($influencer->team_id)->toBe($team->getKey())
        ->and($influencer->name)->toBe('Kayla')
        ->and($influencer->physical_desc)->toContain('blonde')
        ->and($influencer->vibe_words)->toBe(['Streetwear'])
        ->and($influencer->status)->toBe('pending');

    Queue::assertPushed(GenerateInfluencerSetJob::class);
});

it('generates a set of images and marks the influencer ready', function () {
    $user = User::factory()->create();
    $team = influencerTeam($user);
    $provider = ApiProvider::factory()->create(['machine_name' => 'fal', 'type' => ApiProviderType::Media]);
    ProviderConnection::factory()->create([
        'team_id' => $team->getKey(),
        'provider_id' => $provider->getKey(),
        'credentials' => ['api_key' => 'x'],
    ]);
    $influencer = Influencer::factory()->create(['team_id' => $team->getKey(), 'status' => 'pending']);

    Http::fake([
        'https://queue.fal.run/*/requests/*/status' => Http::response(['status' => 'COMPLETED']),
        'https://queue.fal.run/*/requests/*' => Http::response(['images' => [['url' => 'https://cdn.test/img.png']]]),
        'https://queue.fal.run/*' => Http::response(['request_id' => 'req1']),
    ]);

    (new GenerateInfluencerSetJob($influencer, 'fal', 'fal-ai/nano-banana-2', '9:16'))->handle(app(MediaService::class));

    $influencer->refresh();

    expect($influencer->status)->toBe('ready')
        ->and($influencer->generation_history)->toHaveCount(1)
        ->and($influencer->generation_history[0]['images'])->toHaveCount(3)
        ->and($influencer->generation_history[0]['images'][0]['url'])->toBe('https://cdn.test/img.png');
});

it('lets the user pick an avatar', function () {
    $user = User::factory()->create();
    $team = influencerTeam($user);
    $influencer = Influencer::factory()->create([
        'team_id' => $team->getKey(),
        'generation_history' => [[
            'id' => 'set1',
            'provider' => 'fal',
            'model' => 'm',
            'aspect_ratio' => '9:16',
            'status' => 'ready',
            'images' => [['id' => 'img1', 'url' => 'https://cdn.test/a.png', 'prompt' => 'p']],
        ]],
    ]);

    $this->actingAs($user)
        ->post(route('influencers.choose', $influencer), ['image_id' => 'img1'])
        ->assertRedirect(route('influencers.show', $influencer));

    expect($influencer->fresh()->main_image)->toBe('https://cdn.test/a.png');
});

it('forbids viewing another team influencer', function () {
    $user = User::factory()->create();
    influencerTeam($user);
    $other = Influencer::factory()->create();

    $this->actingAs($user)
        ->get(route('influencers.show', $other))
        ->assertNotFound();
});

it('builds three distinct variation prompts', function () {
    $prompts = InfluencerPromptBuilder::buildThreeVariationPrompts([
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => '18',
        'niches' => ['Fashion'],
        'nicheCustom' => '',
        'backstory' => '',
        'personality' => 85,
        'ethnicity' => 'White',
        'skinTone' => 'medium',
        'hairColor' => 'blonde',
        'hairLength' => 'Long',
        'hairTexture' => 'Wavy',
        'eyeColor' => 'blue',
        'build' => 'Athletic',
        'uniqueFeatures' => '',
        'vibeWords' => ['Streetwear'],
        'faceRef' => null,
        'styleRef' => null,
    ]);

    expect($prompts)->toHaveCount(3)
        ->and(array_unique($prompts))->toHaveCount(3)
        ->and($prompts[0])->toContain('Photograph style:');
});
