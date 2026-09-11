<?php

namespace App\Models;

use App\Enums\ApiProviderType;
use Database\Factories\ApiProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'machine_name',
    'friendly_name',
    'base_url',
    'meta',
])]
class ApiProvider extends Model
{
    /** @use HasFactory<ApiProviderFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ApiProviderType::class,
            'meta' => 'array',
        ];
    }
}
