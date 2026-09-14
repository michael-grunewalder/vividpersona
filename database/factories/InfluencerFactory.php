<?php

namespace Database\Factories;

use App\Models\Influencer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Influencer>
 */
class InfluencerFactory extends Factory
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
            'owner_id' => User::factory(),
            'name' => fake()->firstName(),
            'gender' => 'Female',
            'age' => fake()->numberBetween(18, 35),
            'niches' => ['Fashion'],
            'niche_custom' => null,
            'backstory' => null,
            'personality' => 50,
            'ethnicity' => null,
            'skin_tone' => null,
            'hair_color' => null,
            'hair_length' => null,
            'hair_texture' => null,
            'eye_color' => null,
            'build' => null,
            'unique_features' => null,
            'vibe_words' => [],
            'clothing_style' => null,
            'physical_desc' => null,
            'aspect_ratio' => '9:16',
            'provider' => null,
            'model' => null,
            'status' => 'pending',
            'main_image' => null,
            'prompt' => null,
            'generation_history' => [],
            'wardrobe_slots' => [
                ['id' => (string) Str::ulid(), 'name' => 'Wardrobe 1', 'image' => null],
                ['id' => (string) Str::ulid(), 'name' => 'Wardrobe 2', 'image' => null],
                ['id' => (string) Str::ulid(), 'name' => 'Wardrobe 3', 'image' => null],
            ],
            'face_ref_path' => null,
            'style_ref_path' => null,
        ];
    }
}
