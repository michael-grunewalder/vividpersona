<?php

use App\Enums\AiModelType;
use App\Enums\ApiService;
use App\Services\Media\AiModelCatalog;

it('builds a family → version → variant tree for enabled models', function () {
    imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro', 'endpoint' => 'fal-ai/wan/v2.7/pro/text-to-image']);
    imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'standard', 'endpoint' => 'fal-ai/wan/v2.7/text-to-image']);
    imageModel(['family' => 'seedream', 'name' => 'Seedream', 'version' => '4', 'variant' => null, 'endpoint' => 'fal-ai/bytedance/seedream/v4/text-to-image']);
    imageModel(['family' => 'seedream', 'name' => 'Seedream', 'version' => '5', 'variant' => 'lite', 'endpoint' => 'bytedance/seedream/v5/lite/text-to-image']);

    $tree = app(AiModelCatalog::class)->tree(AiModelType::Image, ApiService::Fal);

    $wan = collect($tree)->firstWhere('key', 'wan');
    $seedream = collect($tree)->firstWhere('key', 'seedream');

    expect($wan['versions'])->toHaveCount(1)
        ->and($wan['versions'][0]['variants'])->toHaveCount(2)
        ->and(collect($wan['versions'][0]['variants'])->pluck('key'))->toContain('pro', 'standard');

    expect($seedream['versions'])->toHaveCount(2)
        ->and($seedream['versions'][0]['variants'][0]['label'])->toBe('Seedream 4');
});

it('groups a family without versions into a single version level', function () {
    imageModel(['family' => 'flux', 'name' => 'FLUX', 'version' => null, 'variant' => 'dev', 'endpoint' => 'fal-ai/flux/dev']);
    imageModel(['family' => 'flux', 'name' => 'FLUX', 'version' => null, 'variant' => 'schnell', 'endpoint' => 'fal-ai/flux/schnell']);

    $tree = app(AiModelCatalog::class)->tree(AiModelType::Image, ApiService::Fal);
    $flux = collect($tree)->firstWhere('key', 'flux');

    expect($flux['versions'])->toHaveCount(1)
        ->and($flux['versions'][0]['variants'])->toHaveCount(2);
});

it('ignores disabled models, other types and other providers', function () {
    imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro', 'endpoint' => 'fal-ai/wan/v2.7/pro/text-to-image']);
    imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'standard', 'endpoint' => 'fal-ai/wan/v2.7/text-to-image', 'enabled' => false]);
    imageModel(['family' => 'seedream', 'name' => 'Seedream', 'version' => '5', 'variant' => 'pro', 'provider' => ApiService::WaveSpeed, 'endpoint' => 'bytedance/seedream-v5.0-pro']);
    imageModel(['type' => AiModelType::Video, 'family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => null, 'endpoint' => 'fal-ai/wan/v2.7/text-to-video']);

    $tree = app(AiModelCatalog::class)->tree(AiModelType::Image, ApiService::Fal);

    $wan = collect($tree)->firstWhere('key', 'wan');

    expect($tree)->toHaveCount(1)
        ->and($wan['versions'][0]['variants'])->toHaveCount(1)
        ->and($wan['versions'][0]['variants'][0]['key'])->toBe('pro');
});

it('returns a single tree level per concrete model id', function () {
    $model = imageModel();

    $tree = app(AiModelCatalog::class)->tree(AiModelType::Image, ApiService::Fal);

    expect($tree[0]['versions'][0]['variants'][0]['id'])->toBe($model->getKey());
});

it('finds an enabled model by id', function () {
    $model = imageModel();

    expect(app(AiModelCatalog::class)->find($model->getKey()))->not->toBeNull()
        ->and(app(AiModelCatalog::class)->find('missing'))->toBeNull();
});

it('does not find a disabled model by id', function () {
    $model = imageModel(['enabled' => false]);

    expect(app(AiModelCatalog::class)->find($model->getKey()))->toBeNull();
});

it('returns the first enabled model as the default', function () {
    $first = imageModel(['family' => 'wan', 'name' => 'WAN', 'version' => '2.7', 'variant' => 'pro', 'sort' => 0]);
    imageModel(['family' => 'seedream', 'name' => 'Seedream', 'version' => '5', 'variant' => 'pro', 'endpoint' => 'bytedance/seedream/v5/pro/text-to-image', 'sort' => 1]);
    imageModel(['family' => 'flux', 'name' => 'FLUX', 'version' => null, 'variant' => 'dev', 'endpoint' => 'fal-ai/flux/dev', 'sort' => 0, 'enabled' => false]);

    expect(app(AiModelCatalog::class)->defaultFor(AiModelType::Image, ApiService::Fal)?->getKey())->toBe($first->getKey());
});

it('returns null as the default when no enabled models exist', function () {
    imageModel(['enabled' => false]);

    expect(app(AiModelCatalog::class)->defaultFor(AiModelType::Image, ApiService::Fal))->toBeNull();
});
