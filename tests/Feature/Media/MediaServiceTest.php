<?php

use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('builds the fal payload from the model spec', function (string $model, string $param, string $value) {
    Http::fake([
        'queue.fal.run/*/requests/*/status' => Http::response(['status' => 'COMPLETED']),
        'queue.fal.run/*/requests/*' => Http::response(['images' => [['url' => 'https://cdn.test/img.png']]]),
        'queue.fal.run/*' => Http::response(['request_id' => 'req1']),
    ]);

    $urls = app(MediaService::class)->generateImages('fal', ['api_key' => 'k'], $model, 'a portrait', '9:16');

    expect($urls)->toBe(['https://cdn.test/img.png']);

    Http::assertSent(fn ($request) => $request->url() === 'https://queue.fal.run/'.$model && $request[$param] === $value);
})->with([
    'gpt image 2' => ['openai/gpt-image-2', 'image_size', 'portrait_4_3'],
    'gpt image 1.5' => ['fal-ai/gpt-image-1.5', 'image_size', '1024x1536'],
    'nano banana 2' => ['fal-ai/nano-banana-2', 'aspect_ratio', '9:16'],
    'seedream 4' => ['fal-ai/bytedance/seedream/v4/text-to-image', 'image_size', 'portrait_16_9'],
]);

it('sends landscape sizes and model defaults for 16:9', function () {
    Http::fake([
        'queue.fal.run/*/requests/*/status' => Http::response(['status' => 'COMPLETED']),
        'queue.fal.run/*/requests/*' => Http::response(['images' => [['url' => 'https://cdn.test/img.png']]]),
        'queue.fal.run/*' => Http::response(['request_id' => 'req1']),
    ]);

    app(MediaService::class)->generateImages('fal', ['api_key' => 'k'], 'fal-ai/nano-banana-2', 'a portrait', '16:9');

    Http::assertSent(fn ($request) => $request->url() === 'https://queue.fal.run/fal-ai/nano-banana-2' && $request['aspect_ratio'] === '16:9' && $request['resolution'] === '1K');
});

it('builds the wavespeed payload from the live schema', function () {
    Http::fake([
        'https://api.wavespeed.ai/api/v3/models' => Http::response(['data' => [
            ['model_id' => 'wavespeed-ai/flux-2-dev/text-to-image', 'name' => 'Flux 2 Dev', 'type' => 'text-to-image', 'api_schema' => ['api_schemas' => [['request_schema' => ['properties' => ['size' => ['enum' => ['1280*720', '720*1280']]]]]]]],
        ]], 200),
        'api.wavespeed.ai/api/v3/predictions/*/result' => Http::response(['data' => ['status' => 'completed', 'outputs' => ['https://cdn.test/ws.png']]], 200),
        'api.wavespeed.ai/api/v3/*' => Http::response(['data' => ['id' => 'task1']], 200),
    ]);

    $urls = app(MediaService::class)->generateImages('wavespeed', ['api_key' => 'ws-key'], 'wavespeed-ai/flux-2-dev/text-to-image', 'a portrait', '9:16');

    expect($urls)->toBe(['https://cdn.test/ws.png']);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.wavespeed.ai/api/v3/wavespeed-ai/flux-2-dev/text-to-image' && $request['size'] === '720*1280');
});
