<?php

namespace App\Models;

use Database\Factories\InfluencerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'team_id',
    'owner_id',
    'name',
    'gender',
    'age',
    'niches',
    'niche_custom',
    'backstory',
    'personality',
    'ethnicity',
    'skin_tone',
    'hair_color',
    'hair_length',
    'hair_texture',
    'eye_color',
    'build',
    'unique_features',
    'vibe_words',
    'clothing_style',
    'physical_desc',
    'aspect_ratio',
    'provider',
    'model',
    'status',
    'main_image',
    'prompt',
    'generation_history',
    'wardrobe_slots',
    'face_ref_path',
    'style_ref_path',
])]
class Influencer extends Model
{
    /** @use HasFactory<InfluencerFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'personality' => 'integer',
            'niches' => 'array',
            'vibe_words' => 'array',
            'generation_history' => 'array',
            'wardrobe_slots' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The data shape consumed by the prompt builder.
     *
     * @return array<string, mixed>
     */
    public function promptData(): array
    {
        return [
            'name' => $this->name,
            'gender' => $this->gender,
            'age' => (string) $this->age,
            'niches' => $this->niches ?? [],
            'nicheCustom' => $this->niche_custom ?? '',
            'backstory' => $this->backstory ?? '',
            'personality' => (int) $this->personality,
            'ethnicity' => $this->ethnicity,
            'skinTone' => $this->skin_tone,
            'hairColor' => $this->hair_color,
            'hairLength' => $this->hair_length,
            'hairTexture' => $this->hair_texture,
            'eyeColor' => $this->eye_color,
            'build' => $this->build,
            'uniqueFeatures' => $this->unique_features,
            'vibeWords' => $this->vibe_words ?? [],
            'faceRef' => $this->face_ref_path,
            'styleRef' => $this->style_ref_path,
        ];
    }
}
