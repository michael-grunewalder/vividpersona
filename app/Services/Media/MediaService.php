<?php

namespace App\Services\Media;

use App\Enums\ApiService;
use App\Models\AiModel;
use RuntimeException;

final class MediaService
{
    /**
     * Generate images for a model's provider, returning the resulting urls.
     *
     * @param  array<string, string|null>  $credentials  decrypted team credentials
     * @param  array<int, string>  $references  optional reference image urls
     * @return array<int, string>
     */
    public function generateImages(AiModel $model, array $credentials, string $prompt, string $aspectRatio = '9:16', array $references = []): array
    {
        $payload = $this->buildPayload($model, $prompt, $aspectRatio);

        return match ($model->provider) {
            ApiService::Fal => $this->fal($credentials, $model->endpoint, $payload),
            ApiService::WaveSpeed => $this->wavespeed($credentials, $model->endpoint, $payload),
            default => throw new RuntimeException("Unsupported media provider [{$model->provider->value}]."),
        };
    }

    /**
     * Build the request body for a model using its stored options.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(AiModel $model, string $prompt, string $aspectRatio): array
    {
        $options = $model->options ?? [];

        $size = $options['size'] ?? [];
        $sizes = $size['sizes'] ?? [];

        $payload = array_merge(['prompt' => $prompt], $options['defaults'] ?? []);

        if (($size['param'] ?? null) === 'width_height') {
            $payload['width'] = $sizes[$aspectRatio]['width'] ?? $sizes['9:16']['width'] ?? 720;
            $payload['height'] = $sizes[$aspectRatio]['height'] ?? $sizes['9:16']['height'] ?? 1280;
        } else {
            $payload[$size['param'] ?? 'size'] = $sizes[$aspectRatio] ?? $sizes['9:16'] ?? null;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function fal(array $credentials, string $endpoint, array $payload): array
    {
        $client = new FalClient($this->credential($credentials), config('services.fal.base_url'));

        $requestId = $client->submit($endpoint, $payload);

        return $client->waitForResult($endpoint, $requestId);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function wavespeed(array $credentials, string $endpoint, array $payload): array
    {
        $client = new WaveSpeedClient($this->credential($credentials), config('services.wavespeed.base_url'));

        $taskId = $client->submit($endpoint, $payload);

        return $client->waitForResult($taskId);
    }

    /**
     * @param  array<string, string|null>  $credentials
     */
    private function credential(array $credentials): string
    {
        foreach (['api_key', 'token', 'key'] as $name) {
            if (filled($credentials[$name] ?? null)) {
                return (string) $credentials[$name];
            }
        }

        throw new RuntimeException('The service credential has no API key.');
    }
}
