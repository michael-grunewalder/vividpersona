<?php

namespace App\Models;

use Database\Factories\ProviderConnectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['team_id', 'provider_id', 'credentials'])]
class ProviderConnection extends Model
{
    /** @use HasFactory<ProviderConnectionFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * @return BelongsTo<ApiProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ApiProvider::class, 'provider_id');
    }

    /**
     * Get a single credential value by its meta field name.
     */
    public function credential(string $name): ?string
    {
        return $this->credentials[$name] ?? null;
    }
}
