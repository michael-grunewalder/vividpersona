<?php

namespace Database\Seeders;

use App\Enums\AiModelType;
use App\Enums\ApiService;
use App\Models\AiModel;
use Illuminate\Database\Seeder;

/**
 * Seeds the initial image model catalog. Endpoint ids come from the fal.ai and
 * WaveSpeed model libraries and are meant as a starting point — admins curate
 * and correct them via the backend. WaveSpeed model ids should be validated
 * against GET /api/v3/models with a live key.
 */
class AiModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->models() as $model) {
            AiModel::query()->firstOrCreate(
                ['provider' => $model['provider'], 'endpoint' => $model['endpoint']],
                $model,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function models(): array
    {
        $portrait16 = ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']];
        $portrait43 = ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_4_3', '16:9' => 'landscape_4_3']];
        $aspect = ['param' => 'aspect_ratio', 'sizes' => ['9:16' => '9:16', '16:9' => '16:9']];
        $size = ['param' => 'size', 'sizes' => ['9:16' => '720*1280', '16:9' => '1280*720']];

        return [
            // Qwen Image
            $this->model(AiModelType::Image, 'qwen', 'Qwen Image', '3', null, ApiService::Fal, 'alibaba/qwen-image-3/text-to-image', $portrait43, 0),
            $this->model(AiModelType::Image, 'qwen', 'Qwen Image', '3', null, ApiService::WaveSpeed, 'alibaba/qwen-image-3/text-to-image', $portrait43, 0),

            // WAN text-to-image
            $this->model(AiModelType::Image, 'wan', 'WAN', '2.7', 'standard', ApiService::Fal, 'fal-ai/wan/v2.7/text-to-image', $portrait43, 1),
            $this->model(AiModelType::Image, 'wan', 'WAN', '2.7', 'pro', ApiService::Fal, 'fal-ai/wan/v2.7/pro/text-to-image', $portrait43, 1),
            $this->model(AiModelType::Image, 'wan', 'WAN', '2.1', null, ApiService::WaveSpeed, 'wavespeed-ai/wan-2.1/text-to-image', $size, 1),
            $this->model(AiModelType::Image, 'wan', 'WAN', '2.7', 'pro', ApiService::WaveSpeed, 'alibaba/wan-2.7/text-to-image-pro', $size, 1),

            // Seedream
            $this->model(AiModelType::Image, 'seedream', 'Seedream', '4', null, ApiService::Fal, 'fal-ai/bytedance/seedream/v4/text-to-image', $portrait16, 2),
            $this->model(AiModelType::Image, 'seedream', 'Seedream', '5', 'pro', ApiService::Fal, 'bytedance/seedream/v5/pro/text-to-image', $portrait16, 2),
            $this->model(AiModelType::Image, 'seedream', 'Seedream', '5', 'lite', ApiService::Fal, 'bytedance/seedream/v5/lite/text-to-image', $portrait16, 2),
            $this->model(AiModelType::Image, 'seedream', 'Seedream', '5', 'pro', ApiService::WaveSpeed, 'bytedance/seedream-v5.0-pro', $portrait16, 2),

            // GPT Image
            $this->model(AiModelType::Image, 'gpt-image', 'GPT Image', '1.5', null, ApiService::Fal, 'fal-ai/gpt-image-1.5', ['param' => 'image_size', 'sizes' => ['9:16' => '1024x1536', '16:9' => '1536x1024']], 3),
            $this->model(AiModelType::Image, 'gpt-image', 'GPT Image', '2', null, ApiService::Fal, 'openai/gpt-image-2', $portrait43, 3),
            $this->model(AiModelType::Image, 'gpt-image', 'GPT Image', '2.5', 'flare', ApiService::Fal, 'openai/gpt-image-2.5/flare/text-to-image', $portrait43, 3),
            $this->model(AiModelType::Image, 'gpt-image', 'GPT Image', '2.5', 'sunburst', ApiService::Fal, 'openai/gpt-image-2.5/sunburst/text-to-image', $portrait43, 3),
            $this->model(AiModelType::Image, 'gpt-image', 'GPT Image', '2', null, ApiService::WaveSpeed, 'openai/gpt-image-2/text-to-image', $aspect, 3),

            // FLUX
            $this->model(AiModelType::Image, 'flux', 'FLUX', null, 'dev', ApiService::Fal, 'fal-ai/flux/dev', $portrait16, 4),
            $this->model(AiModelType::Image, 'flux', 'FLUX', null, 'schnell', ApiService::Fal, 'fal-ai/flux/schnell', $portrait16, 4),
            $this->model(AiModelType::Image, 'flux', 'FLUX', '2', 'dev', ApiService::WaveSpeed, 'wavespeed-ai/flux-2-dev/text-to-image', $size, 4),

            // Ideogram
            $this->model(AiModelType::Image, 'ideogram', 'Ideogram', '4', null, ApiService::Fal, 'ideogram/v4', $portrait16, 5),
            $this->model(AiModelType::Image, 'ideogram', 'Ideogram', '3', 'balanced', ApiService::WaveSpeed, 'ideogram-ai/ideogram-v3-balanced', $aspect, 5),
        ];
    }

    /**
     * @param  array{param: string, sizes: array<string, mixed>}|null  $size
     * @return array<string, mixed>
     */
    private function model(
        AiModelType $type,
        string $family,
        string $name,
        ?string $version,
        ?string $variant,
        ApiService $provider,
        string $endpoint,
        ?array $size,
        int $sort,
    ): array {
        $options = [];

        if ($size !== null) {
            $options['size'] = $size;
        }

        $options['defaults'] = ['num_images' => 1];

        return [
            'type' => $type,
            'family' => $family,
            'name' => $name,
            'version' => $version,
            'variant' => $variant,
            'provider' => $provider,
            'endpoint' => $endpoint,
            'options' => $options,
            'enabled' => true,
            'sort' => $sort,
        ];
    }
}
