<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'owner_id'])]
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
     * @return HasMany<ProviderConnection, $this>
     */
    public function connections(): HasMany
    {
        return $this->hasMany(ProviderConnection::class);
    }

    public function connectionFor(ApiProvider $provider): ?ProviderConnection
    {
        return $this->connections()->where('provider_id', $provider->getKey())->first();
    }

    /**
     * The decrypted credential values for a provider, keyed by meta field name.
     *
     * @return array<string, string|null>
     */
    public function providerCredentials(ApiProvider $provider): array
    {
        return $this->connectionFor($provider)?->credentials ?? [];
    }
}
