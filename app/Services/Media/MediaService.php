<?php

namespace App\Services\Media;

use App\Models\ApiProvider;
use RuntimeException;

final class MediaService
{
    /**
     * Generate images for a provider, returning the resulting urls.
     *
     * @param  array<string, string|null>  $credentials  decrypted team credentials
     * @param  array<int, string>  $references  optional reference image urls
     * @return array<int, string>
     */
    public function generateImages(ApiProvider $provider, array $credentials, string $model, string $prompt, string $aspectRatio = '9:16', array $references = []): array
    {
        return match ($provider->machine_name) {
            'fal' => $this->fal($provider, $credentials, $model, $prompt, $aspectRatio),
            'wavespeed' => $this->wavespeed($credentials, $model, $prompt, $aspectRatio),
            default => throw new RuntimeException("Unsupported media provider [{$provider->machine_name}]."),
        };
    }

    private function fal(ApiProvider $provider, array $credentials, string $model, string $prompt, string $aspectRatio): array
    {
        $client = new FalClient($this->credential($credentials), config('services.fal.base_url'));

        $requestId = $client->submit($model, [
            'prompt' => $prompt,
            'image_size' => $aspectRatio === '16:9' ? 'landscape_16_9' : 'portrait_9_16',
            'num_images' => 1,
        ]);

        return $client->waitForResult($model, $requestId);
    }

    private function wavespeed(array $credentials, string $model, string $prompt, string $aspectRatio): array
    {
        $client = new WaveSpeedClient($this->credential($credentials), config('services.wavespeed.base_url'));

        $taskId = $client->submit($model, [
            'prompt' => $prompt,
            'size' => $aspectRatio === '16:9' ? '1280*720' : '720*1280',
        ]);

        return $client->waitForResult($taskId);
    }

    private function credential(array $credentials): string
    {
        foreach (['api_key', 'token', 'key'] as $name) {
            if (filled($credentials[$name] ?? null)) {
                return (string) $credentials[$name];
            }
        }

        throw new RuntimeException('The provider connection has no API key.');
    }
}
