<?php

namespace Database\Factories;

use App\Enums\ApiService;
use App\Models\Team;
use App\Models\TeamApiCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamApiCredential>
 */
class TeamApiCredentialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'service' => ApiService::Fal,
            'credentials' => ['api_key' => 'test-'.fake()->uuid()],
        ];
    }

    /**
     * Target a specific service.
     */
    public function forService(ApiService $service): static
    {
        return $this->state(fn (): array => ['service' => $service]);
    }
}
