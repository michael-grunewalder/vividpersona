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

#[Fillable(['name', 'slug', 'owner_id', 'default_llm', 'default_media_provider', 'storage_limit_bytes', 'storage_used_bytes'])]
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

    /**
     * The team's chosen media provider, falling back to the first connected one.
     */
    public function defaultMediaService(): ?ApiService
    {
        $configured = ApiService::tryFrom((string) $this->default_media_provider);

        if ($configured?->isMedia() && $this->hasCredential($configured)) {
            return $configured;
        }

        foreach (ApiService::media() as $service) {
            if ($this->hasCredential($service)) {
                return $service;
            }
        }

        return null;
    }

    /**
     * The purchased storage quota in bytes, or null when unlimited.
     */
    public function storageLimitBytes(): ?int
    {
        return $this->storage_limit_bytes !== null ? (int) $this->storage_limit_bytes : null;
    }

    /**
     * The number of bytes currently stored for this team.
     */
    public function storageUsedBytes(): int
    {
        return (int) ($this->storage_used_bytes ?? 0);
    }

    /**
     * The bytes still available under the quota, or null when unlimited.
     */
    public function storageRemainingBytes(): ?int
    {
        $limit = $this->storageLimitBytes();

        return $limit === null ? null : max(0, $limit - $this->storageUsedBytes());
    }

    public function hasStorageQuota(): bool
    {
        return $this->storageLimitBytes() !== null;
    }

    /**
     * Whether storing the given number of additional bytes fits the quota.
     */
    public function canStoreBytes(int $bytes): bool
    {
        $limit = $this->storageLimitBytes();

        return $limit === null || $this->storageUsedBytes() + $bytes <= $limit;
    }
}
