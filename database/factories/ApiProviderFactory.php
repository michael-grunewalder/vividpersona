<?php

namespace Database\Factories;

use App\Enums\ApiProviderType;
use App\Models\ApiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiProvider>
 */
class ApiProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ApiProviderType::Llm,
            'machine_name' => fake()->unique()->slug(2),
            'friendly_name' => fake()->company(),
            'base_url' => 'https://api.example.com/v1',
            'meta' => [],
        ];
    }

    /**
     * Indicate that the provider is a media provider.
     */
    public function media(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ApiProviderType::Media,
        ]);
    }
}
