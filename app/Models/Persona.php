<?php

namespace App\Models;

use Database\Factories\PersonaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

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
    'llm_provider',
    'status',
    'last_error',
    'main_image',
    'reference_image_path',
    'prompt',
    'generation_history',
    'wardrobe_slots',
    'face_ref_path',
    'style_ref_path',
])]
class Persona extends Model
{
    /** @use HasFactory<PersonaFactory> */
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
     * The persona's private storage home folder (relative to the private disk).
     */
    public function homeFolder(): string
    {
        return "{$this->team_id}/personas/{$this->getKey()}";
    }

    /**
     * The folder where all persona images are stored.
     */
    public function mediaFolder(): string
    {
        return "{$this->homeFolder()}/media/images";
    }

    /**
     * A signed URL (expires after 20 minutes) for a file in the persona's media
     * folder, used to share images with external APIs as references.
     */
    public function mediaUrl(string $path): string
    {
        return URL::temporarySignedRoute('personas.media', now()->addMinutes(20), [
            'persona' => $this->getKey(),
            'path' => $path,
        ]);
    }

    /**
     * The URL to render the persona's main image: a signed link for local
     * files, the raw URL for legacy remote values.
     */
    public function imageUrl(): ?string
    {
        if (! $this->main_image) {
            return null;
        }

        return str_contains($this->main_image, '://') ? $this->main_image : $this->mediaUrl($this->main_image);
    }

    /**
     * A signed URL for the initial reference image, if one is stored.
     */
    public function referenceImageUrl(): ?string
    {
        return $this->reference_image_path ? $this->mediaUrl($this->reference_image_path) : null;
    }

    /**
     * Whether the given path is a file inside this persona's media folder.
     */
    public function isMediaPath(string $path): bool
    {
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return false;
        }

        return str_starts_with($path, $this->mediaFolder().'/');
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
