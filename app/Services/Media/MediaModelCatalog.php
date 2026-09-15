<?php

namespace App\Services\Media;

use App\Enums\ApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Resolves the selectable image models for a media service and how each model
 * maps the chosen aspect ratio into its request parameters. FAL uses a curated
 * spec from config/services.php; WaveSpeed fetches its live model catalog.
 */
final class MediaModelCatalog
{
    private const WAVESPEED_CACHE_KEY = 'wavespeed.image-models';

    private const CACHE_TTL_SECONDS = 3600;

    /**
     * The selectable models for a service, keyed by model id with labels.
     *
     * @return Collection<int|string, string>
     */
    public function models(ApiService $service, ?string $apiKey = null): Collection
    {
        return collect($this->catalog($service, $apiKey))
            ->map(fn (array $spec, string $id): string => (string) ($spec['label'] ?? $id));
    }

    /**
     * The full request spec for a model: label, size param + portrait/landscape
     * values, and default parameters.
     *
     * @return array<string, mixed>
     */
    public function spec(ApiService $service, string $modelId, ?string $apiKey = null): array
    {
        return $this->catalog($service, $apiKey)[$modelId] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function catalog(ApiService $service, ?string $apiKey = null): array
    {
        return match ($service) {
            ApiService::Fal => (array) config('services.fal.models', []),
            ApiService::WaveSpeed => $this->waveSpeedCatalog($apiKey),
            default => [],
        };
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function waveSpeedCatalog(?string $apiKey): array
    {
        if (($cached = Cache::get(self::WAVESPEED_CACHE_KEY)) !== null) {
            return (array) $cached;
        }

        if (! filled($apiKey)) {
            return [];
        }

        try {
            $catalog = $this->fetchWaveSpeedModels((string) $apiKey);
            Cache::put(self::WAVESPEED_CACHE_KEY, $catalog, self::CACHE_TTL_SECONDS);

            return $catalog;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fetchWaveSpeedModels(string $apiKey): array
    {
        $response = Http::withToken($apiKey)
            ->timeout(10)
            ->get((string) config('services.wavespeed.models_url'))
            ->throw();

        $models = $response->json('data') ?? [];
        $families = (array) config('services.wavespeed.model_families');

        $catalog = [];

        foreach ($models as $model) {
            $id = (string) ($model['model_id'] ?? '');
            $name = (string) ($model['name'] ?? $id);

            if (($model['type'] ?? '') !== 'text-to-image' || $id === '' || ! $this->matchesFamily($id.' '.$name, $families)) {
                continue;
            }

            $schema = $model['api_schema']['api_schemas'][0]['request_schema'] ?? [];

            $catalog[$id] = [
                'label' => $name,
                'size' => $this->deriveSize($schema),
                'defaults' => [],
            ];
        }

        return $catalog;
    }

    /**
     * @param  array<int, string>  $families
     */
    private function matchesFamily(string $haystack, array $families): bool
    {
        $haystack = strtolower($haystack);

        foreach ($families as $family) {
            if (str_contains($haystack, strtolower((string) $family))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Derive the size parameter and portrait/landscape values from the model's
     * request schema, falling back to sensible defaults.
     *
     * @param  array<string, mixed>  $schema
     * @return array{param: string, sizes: array<string, mixed>}
     */
    private function deriveSize(array $schema): array
    {
        $properties = $schema['properties'] ?? [];

        foreach (['aspect_ratio', 'size', 'image_size'] as $param) {
            if (isset($properties[$param])) {
                return [
                    'param' => $param,
                    'sizes' => $this->sizeFromEnum($param, $properties[$param]['enum'] ?? []),
                ];
            }
        }

        if (isset($properties['width']) || isset($properties['height'])) {
            return [
                'param' => 'width_height',
                'sizes' => [
                    '9:16' => ['width' => 720, 'height' => 1280],
                    '16:9' => ['width' => 1280, 'height' => 720],
                ],
            ];
        }

        return ['param' => 'size', 'sizes' => $this->sizeFromEnum('size', [])];
    }

    /**
     * Pick portrait/landscape values from the size enum when possible.
     *
     * @param  array<int, mixed>  $enum
     * @return array<string, mixed>
     */
    private function sizeFromEnum(string $param, array $enum): array
    {
        [$portrait, $landscape] = $this->fallbackSizes($param);

        foreach ($enum as $value) {
            $value = (string) $value;
            $lower = strtolower($value);

            if (preg_match('/^(\d+)[x*](\d+)$/', $value, $m)) {
                if ((int) $m[2] > (int) $m[1]) {
                    $portrait = $value;
                } else {
                    $landscape = $value;
                }
            } elseif (str_contains($lower, 'portrait')) {
                $portrait = $value;
            } elseif (str_contains($lower, 'landscape')) {
                $landscape = $value;
            } elseif ($value === '9:16') {
                $portrait = $value;
            } elseif ($value === '16:9') {
                $landscape = $value;
            }
        }

        return ['9:16' => $portrait, '16:9' => $landscape];
    }

    /**
     * @return array{string, string}
     */
    private function fallbackSizes(string $param): array
    {
        return match ($param) {
            'aspect_ratio' => ['9:16', '16:9'],
            'image_size' => ['portrait_16_9', 'landscape_16_9'],
            default => ['720*1280', '1280*720'],
        };
    }
}
