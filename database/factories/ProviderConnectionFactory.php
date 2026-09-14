<?php

namespace Database\Factories;

use App\Models\ApiProvider;
use App\Models\ProviderConnection;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderConnection>
 */
class ProviderConnectionFactory extends Factory
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
            'provider_id' => ApiProvider::factory(),
            'credentials' => [],
        ];
    }
}
