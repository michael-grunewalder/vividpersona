<?php

use App\Enums\ApiProviderType;
use App\Models\ApiProvider;
use App\Models\User;

it('redirects guests to the login page', function () {
    $this->get('/backend/api-provider')
        ->assertRedirect(route('login'));
});

it('forbids non-super-admins from managing providers', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/backend/api-provider')
        ->assertForbidden();
});

it('allows a super admin to list providers', function () {
    $admin = User::factory()->superAdmin()->create();
    ApiProvider::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get('/backend/api-provider')
        ->assertOk()
        ->assertSee('API Providers');
});

it('creates a provider with credential field definitions', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
            'base_url' => 'https://api.openai.com/v1',
            'meta' => [
                ['name' => 'api_key', 'description' => 'Your API key'],
                ['name' => 'organization', 'description' => 'Your organization id'],
            ],
        ])
        ->assertRedirect(route('backend.api-provider.index'))
        ->assertSessionHas('success');

    $provider = ApiProvider::query()->where('machine_name', 'openai')->firstOrFail();

    expect($provider->getKeyType())->toBe('string')
        ->and(strlen($provider->getKey()))->toBe(26)
        ->and($provider->type)->toBe(ApiProviderType::Llm)
        ->and($provider->friendly_name)->toBe('OpenAI')
        ->and($provider->base_url)->toBe('https://api.openai.com/v1')
        ->and($provider->meta)->toBe([
            ['name' => 'api_key', 'description' => 'Your API key'],
            ['name' => 'organization', 'description' => 'Your organization id'],
        ]);
});

it('drops completely empty credential field rows', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
            'meta' => [
                ['name' => '', 'description' => ''],
                ['name' => 'api_key', 'description' => 'Your API key'],
            ],
        ])
        ->assertRedirect(route('backend.api-provider.index'));

    expect(ApiProvider::query()->where('machine_name', 'openai')->firstOrFail()->meta)
        ->toBe([['name' => 'api_key', 'description' => 'Your API key']]);
});

it('rejects an invalid provider type', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'unknown',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
        ])
        ->assertSessionHasErrors('type');
});

it('rejects a duplicate machine name', function () {
    $admin = User::factory()->superAdmin()->create();
    ApiProvider::factory()->create(['machine_name' => 'openai']);

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
        ])
        ->assertSessionHasErrors('machine_name');
});

it('rejects an invalid machine name format', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'OpenAI!',
            'friendly_name' => 'OpenAI',
        ])
        ->assertSessionHasErrors('machine_name');
});

it('requires a friendly name', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
        ])
        ->assertSessionHasErrors('friendly_name');
});

it('rejects an invalid credential field name', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
            'meta' => [
                ['name' => 'API Key!', 'description' => 'Your API key'],
            ],
        ])
        ->assertSessionHasErrors('meta.0.name');
});

it('rejects duplicate credential field names', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
            'meta' => [
                ['name' => 'api_key', 'description' => 'First'],
                ['name' => 'api_key', 'description' => 'Second'],
            ],
        ])
        ->assertSessionHasErrors('meta.0.name');
});

it('requires a description for each credential field', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
            'meta' => [
                ['name' => 'api_key', 'description' => ''],
            ],
        ])
        ->assertSessionHasErrors('meta.0.description');

    expect(session('errors')->first('meta.0.description'))
        ->toBe('Each credential field needs a description.');
});

it('renders the create form with existing validation errors', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/api-provider', [
            'type' => 'media',
            'machine_name' => 'stability',
            'friendly_name' => 'Stability',
            'meta' => [
                ['name' => 'api_key', 'description' => ''],
            ],
        ])
        ->assertSessionHasErrors('meta.0.description');

    $this->actingAs($admin)
        ->get('/backend/api-provider/create')
        ->assertOk();
});

it('renders the stored credential fields when editing a provider', function () {
    $admin = User::factory()->superAdmin()->create();
    $provider = ApiProvider::factory()->create([
        'machine_name' => 'stability',
        'meta' => [
            ['name' => 'api_key', 'description' => 'Your API key'],
            ['name' => 'organization', 'description' => 'Your organization id'],
        ],
    ]);

    $html = $this->actingAs($admin)
        ->get("/backend/api-provider/{$provider->getKey()}/edit")
        ->assertOk()
        ->getContent();

    expect($html)->toContain('api_key')
        ->and($html)->toContain('organization')
        ->and($html)->toContain('fields: JSON.parse(');
});

it('submits credential fields with paired indices', function () {
    $admin = User::factory()->superAdmin()->create();

    $html = $this->actingAs($admin)
        ->get('/backend/api-provider/create')
        ->assertOk()
        ->getContent();

    expect($html)->toContain(":name=\"'meta['+index+'][name]'\"")
        ->and($html)->toContain(":name=\"'meta['+index+'][description]'\"");
});

it('updates a provider and replaces the credential field definitions', function () {
    $admin = User::factory()->superAdmin()->create();
    $provider = ApiProvider::factory()->create([
        'machine_name' => 'openai',
        'friendly_name' => 'OpenAI',
        'meta' => [['name' => 'old_field', 'description' => 'Old field']],
    ]);

    $this->actingAs($admin)
        ->put("/backend/api-provider/{$provider->getKey()}", [
            'type' => 'media',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI Chat',
            'base_url' => 'https://api.example.com/v2',
            'meta' => [
                ['name' => 'new_field', 'description' => 'New field'],
            ],
        ])
        ->assertRedirect(route('backend.api-provider.index'));

    $provider->refresh();

    expect($provider->type)->toBe(ApiProviderType::Media)
        ->and($provider->friendly_name)->toBe('OpenAI Chat')
        ->and($provider->meta)->toBe([['name' => 'new_field', 'description' => 'New field']]);
});

it('clears the credential field definitions when meta is empty', function () {
    $admin = User::factory()->superAdmin()->create();
    $provider = ApiProvider::factory()->create([
        'machine_name' => 'openai',
        'meta' => [['name' => 'api_key', 'description' => 'Your API key']],
    ]);

    $this->actingAs($admin)
        ->put("/backend/api-provider/{$provider->getKey()}", [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'OpenAI',
            'meta' => [],
        ])
        ->assertRedirect(route('backend.api-provider.index'));

    expect($provider->fresh()->meta)->toBe([]);
});

it('rejects a duplicate machine name on update', function () {
    $admin = User::factory()->superAdmin()->create();
    ApiProvider::factory()->create(['machine_name' => 'openai']);
    $provider = ApiProvider::factory()->create(['machine_name' => 'anthropic']);

    $this->actingAs($admin)
        ->put("/backend/api-provider/{$provider->getKey()}", [
            'type' => 'llm',
            'machine_name' => 'openai',
            'friendly_name' => 'Anthropic',
        ])
        ->assertSessionHasErrors('machine_name');
});

it('deletes a provider', function () {
    $admin = User::factory()->superAdmin()->create();
    $provider = ApiProvider::factory()->create();

    $this->actingAs($admin)
        ->delete("/backend/api-provider/{$provider->getKey()}")
        ->assertRedirect(route('backend.api-provider.index'));

    expect(ApiProvider::query()->find($provider->getKey()))->toBeNull();
});

it('returns 404 for a missing provider', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get('/backend/api-provider/does-not-exist/edit')
        ->assertNotFound();
});
