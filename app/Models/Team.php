<?php

namespace App\Models;

use App\Enums\ApiService;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'owner_id', 'default_llm'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory, HasUlids;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasMany<TeamInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    /**
     * @return HasMany<TeamApiCredential, $this>
     */
    public function apiCredentials(): HasMany
    {
        return $this->hasMany(TeamApiCredential::class);
    }

    public function credentialFor(ApiService $service): ?TeamApiCredential
    {
        return $this->apiCredentials()->where('service', $service->value)->first();
    }

    /**
     * The decrypted credential values for a service, keyed by field name.
     *
     * @return array<string, string|null>
     */
    public function credentialsFor(ApiService $service): array
    {
        return $this->credentialFor($service)?->credentials ?? [];
    }

    public function hasCredential(ApiService $service): bool
    {
        return filled($this->credentialFor($service)?->apiKey());
    }

    /**
     * The team's chosen LLM service, falling back to the first connected one.
     */
    public function defaultLlmService(): ?ApiService
    {
        $configured = ApiService::tryFrom((string) $this->default_llm);

        if ($configured?->isLlm() && $this->hasCredential($configured)) {
            return $configured;
        }

        foreach (ApiService::llm() as $service) {
            if ($this->hasCredential($service)) {
                return $service;
            }
        }

        return null;
    }
}
