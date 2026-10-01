<?php

use App\Enums\ApiService;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Http;

it('builds the fal payload from the model options', function (array $options, string $param, string|int $value) {
    $model = imageModel(['endpoint' => 'openai/gpt-image-2', 'options' => $options]);

    Http::fake([
        'queue.fal.run/*/requests/*/status' => Http::response(['status' => 'COMPLETED']),
        'queue.fal.run/*/requests/*' => Http::response(['images' => [['url' => 'https://cdn.test/img.png']]]),
        'queue.fal.run/*' => Http::response(['request_id' => 'req1']),
    ]);

    $urls = app(MediaService::class)->generateImages($model, ['api_key' => 'k'], 'a portrait', '9:16');

    expect($urls)->toBe(['https://cdn.test/img.png']);

    Http::assertSent(fn ($request) => $request->url() === 'https://queue.fal.run/openai/gpt-image-2' && $request[$param] === $value);
})->with([
    'image_size' => [[
        'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_4_3', '16:9' => 'landscape_4_3']],
        'defaults' => ['num_images' => 1],
    ], 'image_size', 'portrait_4_3'],
    'aspect_ratio' => [[
        'size' => ['param' => 'aspect_ratio', 'sizes' => ['9:16' => '9:16', '16:9' => '16:9']],
        'defaults' => ['num_images' => 1],
    ], 'aspect_ratio', '9:16'],
    'width_height' => [[
        'size' => ['param' => 'width_height', 'sizes' => ['9:16' => ['width' => 720, 'height' => 1280], '16:9' => ['width' => 1280, 'height' => 720]]],
        'defaults' => [],
    ], 'width', 720],
]);

it('sends landscape sizes and model defaults for 16:9', function () {
    $model = imageModel([
        'endpoint' => 'fal-ai/nano-banana-2',
        'options' => [
            'size' => ['param' => 'aspect_ratio', 'sizes' => ['9:16' => '9:16', '16:9' => '16:9']],
            'defaults' => ['num_images' => 1, 'resolution' => '1K'],
        ],
    ]);

    Http::fake([
        'queue.fal.run/*/requests/*/status' => Http::response(['status' => 'COMPLETED']),
        'queue.fal.run/*/requests/*' => Http::response(['images' => [['url' => 'https://cdn.test/img.png']]]),
        'queue.fal.run/*' => Http::response(['request_id' => 'req1']),
    ]);

    app(MediaService::class)->generateImages($model, ['api_key' => 'k'], 'a portrait', '16:9');

    Http::assertSent(fn ($request) => $request->url() === 'https://queue.fal.run/fal-ai/nano-banana-2' && $request['aspect_ratio'] === '16:9' && $request['resolution'] === '1K');
});

it('builds the wavespeed payload from the model options', function () {
    $model = imageModel([
        'provider' => ApiService::WaveSpeed,
        'endpoint' => 'wavespeed-ai/flux-2-dev/text-to-image',
        'options' => [
            'size' => ['param' => 'size', 'sizes' => ['9:16' => '720*1280', '16:9' => '1280*720']],
            'defaults' => [],
        ],
    ]);

    Http::fake([
        'api.wavespeed.ai/api/v3/predictions/*/result' => Http::response(['data' => ['status' => 'completed', 'outputs' => ['https://cdn.test/ws.png']]], 200),
        'api.wavespeed.ai/api/v3/*' => Http::response(['data' => ['id' => 'task1']], 200),
    ]);

    $urls = app(MediaService::class)->generateImages($model, ['api_key' => 'ws-key'], 'a portrait', '9:16');

    expect($urls)->toBe(['https://cdn.test/ws.png']);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.wavespeed.ai/api/v3/wavespeed-ai/flux-2-dev/text-to-image' && $request['size'] === '720*1280');
});
