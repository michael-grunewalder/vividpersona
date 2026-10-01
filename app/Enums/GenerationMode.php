<?php

namespace App\Enums;

/**
 * How the persona wizard generates a set of images.
 */
enum GenerationMode: string
{
    case Default = 'default';
    case Custom = 'custom';
    case ThreeModels = 'three_models';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::Custom => 'Custom model',
            self::ThreeModels => 'Compare 3 models',
        };
    }
}
