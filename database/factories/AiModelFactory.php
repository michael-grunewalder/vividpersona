<?php

namespace Database\Factories;

use App\Enums\AiModelType;
use App\Enums\ApiService;
use App\Models\AiModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiModel>
 */
class AiModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => AiModelType::Image,
            'family' => 'seedream',
            'name' => 'Seedream',
            'version' => '5',
            'variant' => 'pro',
            'provider' => ApiService::Fal,
            'endpoint' => 'bytedance/seedream/v5/pro/text-to-image',
            'options' => [
                'size' => ['param' => 'image_size', 'sizes' => ['9:16' => 'portrait_16_9', '16:9' => 'landscape_16_9']],
                'defaults' => ['num_images' => 1, 'output_format' => 'png'],
            ],
            'enabled' => true,
            'sort' => 0,
        ];
    }

    /**
     * Target a specific model type.
     */
    public function ofType(AiModelType $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }

    /**
     * Target a specific provider.
     */
    public function forProvider(ApiService $provider): static
    {
        return $this->state(fn (): array => ['provider' => $provider]);
    }
}
