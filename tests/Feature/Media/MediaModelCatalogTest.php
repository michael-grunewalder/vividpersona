<?php

use App\Enums\ApiService;
use App\Services\Media\MediaModelCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('serves the curated fal catalog', function () {
    $models = app(MediaModelCatalog::class)->models(ApiService::Fal);

    expect($models->has('openai/gpt-image-2'))->toBeTrue()
        ->and($models->get('openai/gpt-image-2'))->toBe('GPT Image 2')
        ->and($models->has('fal-ai/nano-banana-2'))->toBeTrue()
        ->and($models->has('bytedance/seedream/v5/pro/text-to-image'))->toBeTrue();
});

it('returns a fal model spec with its size mapping', function () {
    $spec = app(MediaModelCatalog::class)->spec(ApiService::Fal, 'openai/gpt-image-2');

    expect($spec['size'])->toBe([
        'param' => 'image_size',
        'sizes' => ['9:16' => 'portrait_4_3', '16:9' => 'landscape_4_3'],
    ]);
});

it('fetches and filters the wavespeed catalog', function () {
    Http::fake([
        'https://api.wavespeed.ai/api/v3/models' => Http::response(['data' => [
            ['model_id' => 'bytedance/seedream-v5.0-pro', 'name' => 'Seedream 5 Pro', 'type' => 'text-to-image', 'api_schema' => ['api_schemas' => [['request_schema' => ['properties' => ['image_size' => ['enum' => ['portrait_16_9', 'landscape_16_9']]]]]]]],
            ['model_id' => 'wavespeed-ai/flux-2-dev/text-to-image', 'name' => 'Flux 2 Dev', 'type' => 'text-to-image', 'api_schema' => ['api_schemas' => [['request_schema' => ['properties' => ['size' => ['enum' => ['1280*720', '720*1280']]]]]]]],
            ['model_id' => 'wavespeed-ai/hunyuan-video/t2v', 'name' => 'Hunyuan Video', 'type' => 'text-to-video', 'api_schema' => []],
            ['model_id' => 'alibaba/qwen-image/text-to-image', 'name' => 'Qwen Image', 'type' => 'text-to-image', 'api_schema' => []],
        ]], 200),
    ]);

    $models = app(MediaModelCatalog::class)->models(ApiService::WaveSpeed, 'ws-key');

    expect($models->keys())
        ->toContain('bytedance/seedream-v5.0-pro')
        ->toContain('wavespeed-ai/flux-2-dev/text-to-image')
        ->not->toContain('wavespeed-ai/hunyuan-video/t2v')
        ->not->toContain('alibaba/qwen-image/text-to-image');
});

it('derives the size mapping from the wavespeed schema', function () {
    Http::fake([
        'https://api.wavespeed.ai/api/v3/models' => Http::response(['data' => [
            ['model_id' => 'bytedance/seedream-v5.0-pro', 'name' => 'Seedream 5 Pro', 'type' => 'text-to-image', 'api_schema' => ['api_schemas' => [['request_schema' => ['properties' => ['aspect_ratio' => ['enum' => ['9:16', '16:9']]]]]]]],
            ['model_id' => 'wavespeed-ai/flux-2-dev/text-to-image', 'name' => 'Flux 2 Dev', 'type' => 'text-to-image', 'api_schema' => ['api_schemas' => [['request_schema' => ['properties' => ['size' => ['enum' => ['1280*720', '720*1280']]]]]]]],
        ]], 200),
    ]);

    $catalog = app(MediaModelCatalog::class);

    expect($catalog->spec(ApiService::WaveSpeed, 'wavespeed-ai/flux-2-dev/text-to-image', 'ws-key')['size'])
        ->toBe(['param' => 'size', 'sizes' => ['9:16' => '720*1280', '16:9' => '1280*720']]);

    expect($catalog->spec(ApiService::WaveSpeed, 'bytedance/seedream-v5.0-pro', 'ws-key')['size'])
        ->toBe(['param' => 'aspect_ratio', 'sizes' => ['9:16' => '9:16', '16:9' => '16:9']]);
});

it('caches the wavespeed catalog', function () {
    Http::fake(['https://api.wavespeed.ai/api/v3/models' => Http::response(['data' => []], 200)]);

    $catalog = app(MediaModelCatalog::class);
    $catalog->models(ApiService::WaveSpeed, 'ws-key');
    $catalog->models(ApiService::WaveSpeed, 'ws-key');

    Http::assertSentCount(1);
});

it('returns no wavespeed models when the fetch fails', function () {
    Http::fake(['https://api.wavespeed.ai/api/v3/models' => Http::response([], 401)]);

    expect(app(MediaModelCatalog::class)->models(ApiService::WaveSpeed, 'bad-key'))->toBeEmpty();
});

it('returns no wavespeed models without a key', function () {
    expect(app(MediaModelCatalog::class)->models(ApiService::WaveSpeed))->toBeEmpty();
});
