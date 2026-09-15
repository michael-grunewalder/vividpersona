<?php

namespace App\Services\Media;

use App\Enums\ApiService;
use RuntimeException;

final class MediaService
{
    public function __construct(private readonly MediaModelCatalog $catalog) {}

    /**
     * Generate images for a media service, returning the resulting urls.
     *
     * @param  array<string, string|null>  $credentials  decrypted team credentials
     * @param  array<int, string>  $references  optional reference image urls
     * @return array<int, string>
     */
    public function generateImages(string $service, array $credentials, string $model, string $prompt, string $aspectRatio = '9:16', array $references = []): array
    {
        $apiService = ApiService::from($service);

        $payload = $this->buildPayload($apiService, $model, $credentials, $prompt, $aspectRatio);

        return match ($apiService) {
            ApiService::Fal => $this->fal($credentials, $model, $payload),
            ApiService::WaveSpeed => $this->wavespeed($credentials, $model, $payload),
            default => throw new RuntimeException("Unsupported media provider [{$service}]."),
        };
    }

    /**
     * Build the request body for a model using its catalog spec.
     *
     * @param  array<string, string|null>  $credentials
     * @return array<string, mixed>
     */
    private function buildPayload(ApiService $service, string $model, array $credentials, string $prompt, string $aspectRatio): array
    {
        $spec = $this->catalog->spec($service, $model, $credentials['api_key'] ?? null);

        $size = $spec['size'] ?? [];
        $sizes = $size['sizes'] ?? [];

        $payload = array_merge(['prompt' => $prompt], $spec['defaults'] ?? []);

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
    private function fal(array $credentials, string $model, array $payload): array
    {
        $client = new FalClient($this->credential($credentials), config('services.fal.base_url'));

        $requestId = $client->submit($model, $payload);

        return $client->waitForResult($model, $requestId);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function wavespeed(array $credentials, string $model, array $payload): array
    {
        $client = new WaveSpeedClient($this->credential($credentials), config('services.wavespeed.base_url'));

        $taskId = $client->submit($model, $payload);

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
