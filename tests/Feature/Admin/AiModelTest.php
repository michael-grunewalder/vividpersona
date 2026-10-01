<?php

use App\Enums\ApiService;
use App\Models\AiModel;
use App\Models\User;

it('redirects guests to the login page', function () {
    $this->get('/backend/ai-model')
        ->assertRedirect(route('login'));
});

it('forbids non-super-admins from managing AI models', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/backend/ai-model')
        ->assertForbidden();
});

it('allows a super admin to list AI models', function () {
    $admin = User::factory()->superAdmin()->create();
    imageModel(['name' => 'WAN']);

    $this->actingAs($admin)
        ->get('/backend/ai-model')
        ->assertOk()
        ->assertSee('AI models')
        ->assertSee('WAN');
});

it('creates an AI model', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/ai-model', [
            'type' => 'image',
            'family' => 'wan',
            'name' => 'WAN',
            'version' => '2.7',
            'variant' => 'pro',
            'provider' => 'fal',
            'endpoint' => 'fal-ai/wan/v2.7/pro/text-to-image',
            'options' => json_encode([
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1],
            ]),
            'enabled' => 1,
            'sort' => 3,
        ])
        ->assertRedirect(route('backend.ai-model.index'));

    $model = AiModel::query()->where('endpoint', 'fal-ai/wan/v2.7/pro/text-to-image')->firstOrFail();

    expect($model->family)->toBe('wan')
        ->and($model->variant)->toBe('pro')
        ->and($model->enabled)->toBeTrue()
        ->and($model->options['defaults']['num_images'])->toBe(1);
});

it('rejects a media provider for an invalid endpoint combination', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/ai-model', [
            'type' => 'image',
            'family' => 'wan',
            'name' => 'WAN',
            'provider' => 'claude',
            'endpoint' => 'fal-ai/wan/v2.7/text-to-image',
        ])
        ->assertSessionHasErrors('provider');
});

it('enforces a unique endpoint per provider', function () {
    $admin = User::factory()->superAdmin()->create();
    imageModel(['provider' => ApiService::Fal, 'endpoint' => 'fal-ai/nano-banana-2']);

    $this->actingAs($admin)
        ->post('/backend/ai-model', [
            'type' => 'image',
            'family' => 'nano-banana',
            'name' => 'Nano Banana',
            'provider' => 'fal',
            'endpoint' => 'fal-ai/nano-banana-2',
        ])
        ->assertSessionHasErrors('endpoint');
});

it('allows the same endpoint for a different provider', function () {
    $admin = User::factory()->superAdmin()->create();
    imageModel(['provider' => ApiService::Fal, 'endpoint' => 'alibaba/qwen-image-3/text-to-image']);

    $this->actingAs($admin)
        ->post('/backend/ai-model', [
            'type' => 'image',
            'family' => 'qwen',
            'name' => 'Qwen Image',
            'provider' => 'wavespeed',
            'endpoint' => 'alibaba/qwen-image-3/text-to-image',
        ])
        ->assertRedirect(route('backend.ai-model.index'));
});

it('updates an AI model', function () {
    $admin = User::factory()->superAdmin()->create();
    $model = imageModel();

    $this->actingAs($admin)
        ->put(route('backend.ai-model.update', $model), [
            'type' => 'image',
            'family' => 'wan',
            'name' => 'WAN',
            'version' => '2.7',
            'variant' => 'standard',
            'provider' => 'fal',
            'endpoint' => 'fal-ai/wan/v2.7/text-to-image',
            'enabled' => 0,
        ])
        ->assertRedirect(route('backend.ai-model.index'));

    $model->refresh();

    expect($model->family)->toBe('wan')
        ->and($model->variant)->toBe('standard')
        ->and($model->enabled)->toBeFalse();
});

it('deletes an AI model', function () {
    $admin = User::factory()->superAdmin()->create();
    $model = imageModel();

    $this->actingAs($admin)
        ->delete(route('backend.ai-model.destroy', $model))
        ->assertRedirect(route('backend.ai-model.index'));

    expect(AiModel::query()->find($model->getKey()))->toBeNull();
});
