<?php

namespace App\Models;

use App\Enums\ApiService;
use Database\Factories\TeamApiCredentialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['team_id', 'service', 'credentials'])]
class TeamApiCredential extends Model
{
    /** @use HasFactory<TeamApiCredentialFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service' => ApiService::class,
            'credentials' => 'encrypted:array',
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
     * Get a single credential value by field name.
     */
    public function credential(string $name): ?string
    {
        return $this->credentials[$name] ?? null;
    }

    /**
     * The service's API key, if present.
     */
    public function apiKey(): ?string
    {
        return $this->credential('api_key');
    }
}
