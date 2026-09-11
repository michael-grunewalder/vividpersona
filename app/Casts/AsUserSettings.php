<?php

namespace App\Casts;

use App\ValueObjects\UserSettings;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AsUserSettings implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): UserSettings
    {
        $data = is_string($value) ? json_decode($value, true) : $value;

        return UserSettings::fromArray(is_array($data) ? $data : null);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof UserSettings) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode(UserSettings::fromArray($value)->toArray());
        }

        if ($value === null) {
            return json_encode((new UserSettings)->toArray());
        }

        throw new InvalidArgumentException('The given value must be a UserSettings instance or an array.');
    }
}
