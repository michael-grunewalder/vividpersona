<?php

namespace Database\Factories;

use App\Enums\TeamInvitationStatus;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamInvitation>
 */
class TeamInvitationFactory extends Factory
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
            'email' => fake()->safeEmail(),
            'name' => fake()->name(),
            'role' => TeamRole::Editor,
            'status' => TeamInvitationStatus::Pending,
            'created_by' => null,
        ];
    }
}
