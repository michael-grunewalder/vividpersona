<?php

namespace App\Models;

use App\Enums\AiModelType;
use App\Enums\ApiService;
use Database\Factories\AiModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type',
    'family',
    'name',
    'version',
    'variant',
    'provider',
    'endpoint',
    'options',
    'enabled',
    'sort',
])]
class AiModel extends Model
{
    /** @use HasFactory<AiModelFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AiModelType::class,
            'provider' => ApiService::class,
            'options' => 'array',
            'enabled' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeOfType(Builder $query, AiModelType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function scopeForProvider(Builder $query, ApiService $provider): Builder
    {
        return $query->where('provider', $provider->value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('family')->orderBy('name');
    }

    /**
     * @return HasMany<Persona, $this>
     */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class);
    }

    /**
     * A short human label, e.g. "WAN 2.7 Pro".
     */
    public function label(): string
    {
        return trim(implode(' ', array_filter([
            $this->name,
            $this->version,
            $this->variant,
        ])));
    }
}
