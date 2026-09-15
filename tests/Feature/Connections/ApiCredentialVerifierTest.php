<?php

use App\Enums\ApiService;
use App\Services\Connections\ApiCredentialVerifier;
use Illuminate\Support\Facades\Http;

it('accepts a valid key for each service', function (ApiService $service, string $url, array $response) {
    Http::fake([$url => Http::response($response, 200)]);

    expect(app(ApiCredentialVerifier::class)->verify($service, 'key'))->toBeTrue();
})->with([
    'fal' => [ApiService::Fal, 'https://fal.run/users/me', ['username' => 'x']],
    'wavespeed' => [ApiService::WaveSpeed, 'https://api.wavespeed.ai/api/v3/balance', ['code' => 200, 'data' => ['balance' => 50.25]]],
    'claude' => [ApiService::Claude, 'https://api.anthropic.com/v1/models', ['data' => []]],
    'chatgpt' => [ApiService::ChatGpt, 'https://api.openai.com/v1/models', ['data' => []]],
    'deepseek' => [ApiService::DeepSeek, 'https://api.deepseek.com/user/balance', ['balance_infos' => []]],
]);

it('rejects an invalid key', function (ApiService $service, string $url) {
    Http::fake([$url => Http::response([], 401)]);

    expect(app(ApiCredentialVerifier::class)->verify($service, 'bad-key'))->toBeFalse();
})->with([
    'fal' => [ApiService::Fal, 'https://fal.run/users/me'],
    'claude' => [ApiService::Claude, 'https://api.anthropic.com/v1/models'],
    'chatgpt' => [ApiService::ChatGpt, 'https://api.openai.com/v1/models'],
    'deepseek' => [ApiService::DeepSeek, 'https://api.deepseek.com/user/balance'],
]);

it('rejects a wavespeed response without a success code', function () {
    Http::fake(['https://api.wavespeed.ai/api/v3/balance' => Http::response(['code' => 401], 200)]);

    expect(app(ApiCredentialVerifier::class)->verify(ApiService::WaveSpeed, 'key'))->toBeFalse();
});

it('returns false when the request throws', function () {
    Http::fake(['https://fal.run/users/me' => fn () => throw new Exception('boom')]);

    expect(app(ApiCredentialVerifier::class)->verify(ApiService::Fal, 'key'))->toBeFalse();
});
