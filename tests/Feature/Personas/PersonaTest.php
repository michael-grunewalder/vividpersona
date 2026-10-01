<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\ApiService;
use App\Jobs\GenerateImageJob;
use App\Models\Persona;
use App\Models\Team;
use App\Models\TeamApiCredential;
use App\Models\User;
use App\Services\Media\MediaService;
use App\Support\Prompt\PersonaPromptBuilder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function personaTeam(User $user): Team
{
    $team = (new CreateTeam)->handle($user, 'Acme', 'owner');
    $user->setCurrentTeam($team);

    return $team;
}

it('redirects guests to the login page', function () {
    $this->get('/personas')->assertRedirect(route('login'));
});

it('lists the current team personas only', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    Persona::factory()->create(['team_id' => $team->getKey(), 'name' => 'Kayla']);
    Persona::factory()->create(['name' => 'Outsider']);

    $this->actingAs($user)
        ->get('/personas')
        ->assertOk()
        ->assertSee('Kayla')
        ->assertDontSee('Outsider');
});

it('renders the create wizard', function () {
    $user = User::factory()->create();
    personaTeam($user);

    $this->actingAs($user)
        ->get('/personas/create')
        ->assertOk()
        ->assertSee('Basics');
});

it('creates an persona for the current team and dispatches generation', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 85,
        'vibe_words' => 'Streetwear',
        'niches' => ['Fashion'],
        'aspect_ratio' => '9:16',
        'mode' => 'custom',
        'ai_model_id' => $model->getKey(),
        'ethnicity' => 'White',
        'skin_tone' => 'medium',
        'hair_color' => 'blonde',
        'hair_length' => 'Long',
        'hair_texture' => 'Wavy',
        'eye_color' => 'blue',
        'build' => 'Athletic',
    ])->assertRedirect();

    $persona = Persona::query()->firstOrFail();

    expect($persona->team_id)->toBe($team->getKey())
        ->and($persona->name)->toBe('Kayla')
        ->and($persona->physical_desc)->toContain('blonde')
        ->and($persona->vibe_words)->toBe(['Streetwear'])
        ->and($persona->ai_model_id)->toBe($model->getKey())
        ->and($persona->provider)->toBe('fal')
        ->and($persona->model)->toBe($model->endpoint)
        ->and($persona->status)->toBe('generating')
        ->and($persona->generation_history)->toHaveCount(1)
        ->and($persona->generation_history[0]['status'])->toBe('generating')
        ->and($persona->generation_history[0]['total'])->toBe(3);

    Queue::assertPushed(GenerateImageJob::class, 3);
});

it('rejects a model that is not offered by the team default provider', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel(['provider' => ApiService::WaveSpeed, 'endpoint' => 'wavespeed-ai/flux-2-dev/text-to-image']);

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'aspect_ratio' => '9:16',
        'mode' => 'custom',
        'ai_model_id' => $model->getKey(),
    ])->assertStatus(422);

    expect(Persona::query()->count())->toBe(0);
});

it('rejects a disabled model', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel(['enabled' => false]);

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'aspect_ratio' => '9:16',
        'mode' => 'custom',
        'ai_model_id' => $model->getKey(),
    ])->assertStatus(422);

    expect(Persona::query()->count())->toBe(0);
});

it('uses the team default model in default mode', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $default = imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro']);
    imageModel(['family' => 'seedream', 'name' => 'Seedream', 'version' => '5', 'variant' => 'pro', 'endpoint' => 'bytedance/seedream/v5/pro/text-to-image', 'sort' => 1]);

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'aspect_ratio' => '9:16',
        'mode' => 'default',
    ])->assertRedirect();

    $persona = Persona::query()->firstOrFail();

    expect($persona->ai_model_id)->toBe($default->getKey())
        ->and($persona->generation_history[0]['mode'])->toBe('default')
        ->and($persona->generation_history[0]['models'][0]['ai_model_id'])->toBe($default->getKey())
        ->and($persona->generation_history[0]['models'])->toHaveCount(1)
        ->and($persona->generation_history[0]['total'])->toBe(3);

    Queue::assertPushed(GenerateImageJob::class, 3);
});

it('generates one image per model in three-models mode', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $wan = imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro']);
    $seedream = imageModel(['family' => 'seedream', 'name' => 'Seedream', 'version' => '5', 'variant' => 'pro', 'endpoint' => 'bytedance/seedream/v5/pro/text-to-image']);
    $flux = imageModel(['family' => 'flux', 'name' => 'FLUX', 'version' => null, 'variant' => 'dev', 'endpoint' => 'fal-ai/flux/dev']);

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'aspect_ratio' => '9:16',
        'mode' => 'three_models',
        'ai_model_ids' => [$wan->getKey(), $seedream->getKey(), $flux->getKey()],
    ])->assertRedirect();

    $persona = Persona::query()->firstOrFail();

    $set = $persona->generation_history[0];

    expect($set['mode'])->toBe('three_models')
        ->and($set['models'])->toHaveCount(3)
        ->and(collect($set['models'])->pluck('ai_model_id')->all())->toBe([$wan->getKey(), $seedream->getKey(), $flux->getKey()])
        ->and($set['total'])->toBe(3)
        ->and($persona->ai_model_id)->toBe($wan->getKey());

    Queue::assertPushed(GenerateImageJob::class, 3);

    $pushed = Queue::pushed(GenerateImageJob::class)->map(fn ($job) => $job->aiModel->getKey());
    expect($pushed)->toHaveCount(3)
        ->and($pushed->all())->toBe([$wan->getKey(), $seedream->getKey(), $flux->getKey()]);
});

it('allows the same model on multiple three-model slots', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'aspect_ratio' => '9:16',
        'mode' => 'three_models',
        'ai_model_ids' => [$model->getKey(), $model->getKey(), $model->getKey()],
    ])->assertRedirect();

    $persona = Persona::query()->firstOrFail();

    expect($persona->generation_history[0]['total'])->toBe(3);

    Queue::assertPushed(GenerateImageJob::class, 3);
});

it('requires three models in three-models mode', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'aspect_ratio' => '9:16',
        'mode' => 'three_models',
        'ai_model_ids' => [$model->getKey(), $model->getKey()],
    ])->assertSessionHasErrors('ai_model_ids');

    expect(Persona::query()->count())->toBe(0);
});

it('generates an extra set with a different model', function () {
    Queue::fake();

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);

    $this->actingAs($user)
        ->post(route('personas.generate-set', $persona), [
            'mode' => 'custom',
            'ai_model_id' => $model->getKey(),
            'aspect_ratio' => '16:9',
        ])
        ->assertRedirect();

    $persona->refresh();

    expect($persona->status)->toBe('generating')
        ->and($persona->generation_history)->toHaveCount(1)
        ->and($persona->generation_history[0]['mode'])->toBe('custom')
        ->and($persona->generation_history[0]['models'][0]['ai_model_id'])->toBe($model->getKey())
        ->and($persona->generation_history[0]['models'][0]['label'])->toBe($model->label())
        ->and($persona->generation_history[0]['aspect_ratio'])->toBe('16:9');

    Queue::assertPushed(GenerateImageJob::class, 3);
});

it('shows the model family cascade in the wizard when models exist', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro']);

    $this->actingAs($user)
        ->get('/personas/create')
        ->assertOk()
        ->assertSee('WAN');
});

it('renders the generate-more model selector on the show page', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro']);
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);

    $this->actingAs($user)
        ->get(route('personas.show', $persona))
        ->assertOk()
        ->assertSee('WAN')
        ->assertSee('Generate another set');
});

it('renders the per-image model banner overlay', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    $model = imageModel();
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'generation_history' => [[
            'id' => 'set1',
            'mode' => 'custom',
            'models' => [['ai_model_id' => $model->getKey(), 'provider' => 'fal', 'endpoint' => $model->endpoint, 'label' => $model->label()]],
            'aspect_ratio' => '9:16',
            'status' => 'ready',
            'images' => [['id' => 'img1', 'url' => 'https://cdn.test/a.png', 'prompt' => 'p', 'model' => $model->label()]],
        ]],
    ]);

    $this->actingAs($user)
        ->get(route('personas.show', $persona))
        ->assertOk()
        ->assertSee('bg-black/50')
        ->assertSee('image.model');
});

it('generates a set of images and marks the persona ready', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();
    $setId = 'set1';
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'status' => 'generating',
        'generation_history' => [[
            'id' => $setId,
            'ai_model_id' => $model->getKey(),
            'provider' => 'fal',
            'model' => $model->label(),
            'endpoint' => $model->endpoint,
            'aspect_ratio' => '9:16',
            'status' => 'generating',
            'images' => [],
            'total' => 3,
        ]],
    ]);

    Http::fake([
        'https://queue.fal.run/*/requests/*/status' => Http::response(['status' => 'COMPLETED']),
        'https://queue.fal.run/*/requests/*' => Http::response(['images' => [['url' => 'https://cdn.test/img.png']]]),
        'https://queue.fal.run/*' => Http::response(['request_id' => 'req1']),
    ]);

    foreach (['p1', 'p2', 'p3'] as $prompt) {
        (new GenerateImageJob($persona, $setId, $model, '9:16', $prompt))->handle(app(MediaService::class));
    }

    $persona->refresh();

    expect($persona->status)->toBe('ready')
        ->and($persona->last_error)->toBeNull()
        ->and($persona->generation_history)->toHaveCount(1)
        ->and($persona->generation_history[0]['status'])->toBe('ready')
        ->and($persona->generation_history[0]['images'])->toHaveCount(3)
        ->and($persona->generation_history[0]['images'][0]['url'])->toBe('https://cdn.test/img.png')
        ->and($persona->generation_history[0]['images'][0]['model'])->toBe($model->label());
});

it('marks the persona failed when the service has no credential', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    $model = imageModel();
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'status' => 'generating',
        'generation_history' => [['id' => 'set1', 'status' => 'generating', 'images' => [], 'total' => 3]],
    ]);

    (new GenerateImageJob($persona, 'set1', $model, '9:16', 'p'))->handle(app(MediaService::class));

    $persona->refresh();

    expect($persona->status)->toBe('failed')
        ->and($persona->last_error)->not->toBeNull();
});

it('keeps a set when an individual image fails', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'status' => 'generating',
        'generation_history' => [['id' => 'set1', 'status' => 'generating', 'images' => [], 'total' => 3]],
    ]);

    Http::fakeSequence()
        ->push(['request_id' => 'req1'])
        ->push(['status' => 'FAILED']);

    (new GenerateImageJob($persona, 'set1', $model, '9:16', 'p'))->handle(app(MediaService::class));

    $persona->refresh();

    expect($persona->status)->toBe('generating')
        ->and($persona->generation_history[0]['status'])->toBe('generating')
        ->and($persona->generation_history[0]['images'])->toHaveCount(1)
        ->and($persona->generation_history[0]['images'][0]['url'])->toBeNull()
        ->and($persona->generation_history[0]['images'][0]['error'])->not->toBeNull();
});

it('reports the status including last_error', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'status' => 'failed',
        'last_error' => 'No API key connected.',
    ]);

    $this->actingAs($user)
        ->getJson(route('personas.status', $persona))
        ->assertOk()
        ->assertJson(['status' => 'failed', 'last_error' => 'No API key connected.']);
});

it('lets the user pick an image and stores it as the only reference', function () {
    Storage::fake('private');

    Http::fake([
        'https://cdn.test/a.png' => Http::response('image-bytes', 200, ['Content-Type' => 'image/png']),
    ]);

    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'generation_history' => [[
            'id' => 'set1',
            'provider' => 'fal',
            'model' => 'm',
            'aspect_ratio' => '9:16',
            'status' => 'ready',
            'images' => [
                ['id' => 'img1', 'url' => 'https://cdn.test/a.png', 'prompt' => 'p'],
                ['id' => 'img2', 'url' => 'https://cdn.test/b.png', 'prompt' => 'p'],
            ],
        ]],
    ]);

    $this->actingAs($user)
        ->post(route('personas.choose', $persona), ['image_id' => 'img1'])
        ->assertRedirect(route('personas.show', $persona));

    $persona->refresh();

    expect($persona->reference_image_path)->not->toBeNull()
        ->and($persona->main_image)->toBe($persona->reference_image_path)
        ->and($persona->generation_history)->toBe([])
        ->and($persona->reference_image_path)->toStartWith($persona->mediaFolder().'/')
        ->and($persona->reference_image_path)->toEndWith('.png');

    expect(Storage::disk('private')->exists($persona->reference_image_path))->toBeTrue()
        ->and(Storage::disk('private')->get($persona->reference_image_path))->toBe('image-bytes');

    expect($team->fresh()->storageUsedBytes())->toBe(strlen('image-bytes'));
});

it('rejects picking an image twice (locked after pick)', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create([
        'team_id' => $team->getKey(),
        'main_image' => 'media/images/already.png',
        'reference_image_path' => 'media/images/already.png',
    ]);

    $this->actingAs($user)
        ->post(route('personas.choose', $persona), ['image_id' => 'img1'])
        ->assertSessionHasErrors('image_id');

    expect($persona->fresh()->reference_image_path)->not->toBeNull();
});

it('lets the user delete a persona', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);

    $this->actingAs($user)
        ->delete(route('personas.destroy', $persona))
        ->assertRedirect(route('personas.index'));

    expect(Persona::query()->find($persona->getKey()))->toBeNull();
});

it('forbids deleting another team persona', function () {
    $user = User::factory()->create();
    personaTeam($user);
    $other = Persona::factory()->create();

    $this->actingAs($user)
        ->delete(route('personas.destroy', $other))
        ->assertNotFound();

    expect(Persona::query()->find($other->getKey()))->not->toBeNull();
});

it('forbids viewing another team persona', function () {
    $user = User::factory()->create();
    personaTeam($user);
    $other = Persona::factory()->create();

    $this->actingAs($user)
        ->get(route('personas.show', $other))
        ->assertNotFound();
});

it('builds three distinct variation prompts with different personas', function () {
    $prompts = PersonaPromptBuilder::buildThreeVariationPrompts([
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

    $shapes = ['round face', 'oval face', 'heart-shaped face', 'square jaw', 'angular face', 'narrow face'];

    $found = array_map(fn (string $prompt) => collect($shapes)->first(fn (string $shape) => str_contains($prompt, $shape)), $prompts);

    expect($prompts)->toHaveCount(3)
        ->and(array_unique($prompts))->toHaveCount(3)
        ->and($prompts[0])->toContain('Photograph style:');

    // the selected look is preserved in every sample
    foreach ($prompts as $prompt) {
        expect($prompt)->toContain('wavy blonde hair')
            ->toContain('blue eyes');
    }

    // each sample is a different person (distinct facial identity)
    expect(array_filter($found))->toHaveCount(3)
        ->and(array_unique(array_filter($found)))->toHaveCount(3);
});

it('keeps the same face for all samples when a face reference is provided', function () {
    $prompts = PersonaPromptBuilder::buildThreeVariationPrompts([
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
        'faceRef' => 'persona-refs/face.png',
        'styleRef' => null,
    ]);

    foreach ($prompts as $prompt) {
        expect($prompt)->not->toContain(', with ');
    }
});

it('serves a persona media file through a valid signed link', function () {
    Storage::fake('private');

    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);
    $path = $persona->mediaFolder().'/face.png';

    Storage::disk('private')->put($path, 'image-bytes');

    $url = URL::temporarySignedRoute('personas.media', now()->addMinutes(20), [
        'persona' => $persona->getKey(),
        'path' => $path,
    ]);

    $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('rejects unsigned or expired persona media links', function () {
    Storage::fake('private');

    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);
    $path = $persona->mediaFolder().'/face.png';

    Storage::disk('private')->put($path, 'image-bytes');

    // Unsigned (plain route URL).
    $this->get(route('personas.media', ['persona' => $persona, 'path' => $path]))
        ->assertNotFound();

    // Expired signature.
    $expired = URL::temporarySignedRoute('personas.media', now()->subMinute(), [
        'persona' => $persona->getKey(),
        'path' => $path,
    ]);

    $this->get($expired)->assertNotFound();
});

it('rejects a signed link outside the persona media folder', function () {
    Storage::fake('private');

    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);

    Storage::disk('private')->put('other/file.png', 'image-bytes');

    $url = URL::temporarySignedRoute('personas.media', now()->addMinutes(20), [
        'persona' => $persona->getKey(),
        'path' => 'other/file.png',
    ]);

    $this->get($url)->assertNotFound();
});

it('stores reference uploads in the private persona media folder', function () {
    Storage::fake('private');

    $user = User::factory()->create();
    $team = personaTeam($user);
    TeamApiCredential::factory()->forService(ApiService::Fal)->create(['team_id' => $team->getKey()]);
    $model = imageModel();
    Queue::fake();

    $this->actingAs($user)->post('/personas', [
        'name' => 'Kayla',
        'gender' => 'Female',
        'age' => 18,
        'personality' => 50,
        'niches' => ['Fashion'],
        'aspect_ratio' => '9:16',
        'mode' => 'custom',
        'ai_model_id' => $model->getKey(),
        'face_ref' => UploadedFile::fake()->image('face.jpg'),
    ])->assertRedirect();

    $persona = Persona::query()->firstOrFail();

    expect($persona->face_ref_path)->toStartWith($persona->mediaFolder().'/')
        ->and(Storage::disk('private')->exists($persona->face_ref_path))->toBeTrue()
        ->and($team->fresh()->storageUsedBytes())->toBeGreaterThan(0);
});

it('enforces the team storage limit when picking an image', function () {
    Storage::fake('private');

    Http::fake(['https://cdn.test/a.png' => Http::response('image-bytes', 200, ['Content-Type' => 'image/png'])]);

    $user = User::factory()->create();
    $team = personaTeam($user);
    $team->update(['storage_limit_bytes' => 5]);
    $persona = Persona::factory()->create([
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
        ->post(route('personas.choose', $persona), ['image_id' => 'img1'])
        ->assertSessionHasErrors('image_id');

    expect($persona->fresh()->reference_image_path)->toBeNull()
        ->and(Storage::disk('private')->files($persona->mediaFolder()))->toBeEmpty()
        ->and($team->fresh()->storageUsedBytes())->toBe(0);
});

it('removes stored media and usage when a persona is deleted', function () {
    Storage::fake('private');

    $user = User::factory()->create();
    $team = personaTeam($user);
    $persona = Persona::factory()->create(['team_id' => $team->getKey()]);
    $path = $persona->mediaFolder().'/ref.png';
    Storage::disk('private')->put($path, 'image-bytes');
    $team->update(['storage_used_bytes' => strlen('image-bytes')]);

    $this->actingAs($user)
        ->delete(route('personas.destroy', $persona))
        ->assertRedirect(route('personas.index'));

    expect(Storage::disk('private')->exists($path))->toBeFalse()
        ->and($team->fresh()->storageUsedBytes())->toBe(0);
});

it('updates the team storage limit', function () {
    $user = User::factory()->create();
    $team = personaTeam($user);

    $this->actingAs($user)
        ->patch(route('teams.storage.update', $team), ['storage_limit_gb' => 100])
        ->assertRedirect(route('teams.show', $team));

    expect($team->fresh()->storageLimitBytes())->toBe(100 * 1024 * 1024 * 1024);

    $this->actingAs($user)
        ->patch(route('teams.storage.update', $team), ['storage_limit_gb' => ''])
        ->assertRedirect(route('teams.show', $team));

    expect($team->fresh()->storageLimitBytes())->toBeNull();
});
