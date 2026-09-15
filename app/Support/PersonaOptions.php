<?php

namespace App\Support;

/**
 * Selectable option lists for the persona wizard. Add new values here
 * to extend the UI; see docs/persona-prompting.md.
 */
final class PersonaOptions
{
    public const GENDERS = ['Female', 'Male'];

    public const NICHES = [
        'Fashion', 'Beauty', 'Lifestyle', 'Fitness', 'Travel', 'Food & Dining',
        'Tech', 'Gaming', 'Finance', 'Entertainment', 'Wellness', 'Sports', 'Other',
    ];

    /** @var array<int, array{id: string, sub: string, genders: array<int, string>}> */
    public const VIBES = [
        ['id' => 'Minimalist', 'sub' => 'Clean, simple, less is more', 'genders' => []],
        ['id' => 'Old Money', 'sub' => 'Understated wealth & heritage', 'genders' => []],
        ['id' => 'Clean Girl', 'sub' => 'Effortless, dewy, no-makeup look', 'genders' => ['Female']],
        ['id' => 'Editorial', 'sub' => 'High fashion, bold & structured', 'genders' => []],
        ['id' => 'Streetwear', 'sub' => 'Urban, casual street style', 'genders' => []],
        ['id' => 'Bohemian', 'sub' => 'Earthy, flowy, free-spirited', 'genders' => []],
        ['id' => 'Glam', 'sub' => 'Dressy, dramatic & glamorous', 'genders' => []],
        ['id' => 'Preppy', 'sub' => 'Classic, collegiate, polished', 'genders' => []],
        ['id' => 'Sporty', 'sub' => 'Athletic & activewear vibes', 'genders' => []],
        ['id' => 'Dark & Moody', 'sub' => 'Alternative, edgy & dramatic', 'genders' => []],
        ['id' => 'Y2K', 'sub' => '2000s nostalgia & pop culture', 'genders' => []],
        ['id' => 'Cottagecore', 'sub' => 'Romantic, vintage & nature', 'genders' => ['Female']],
        ['id' => 'Tech Bro', 'sub' => 'Smart-casual, Silicon Valley', 'genders' => ['Male']],
        ['id' => 'Coastal', 'sub' => 'Linen, nautical, effortlessly sun-worn', 'genders' => []],
    ];

    public const SKIN_TONES = ['fair', 'light', 'medium', 'tan', 'brown', 'deep', 'ebony'];

    public const HAIR_COLORS = ['blonde', 'brunette', 'black', 'auburn', 'red', 'silver', 'dyed'];

    public const HAIR_LENGTHS_FEMALE = ['Short', 'Medium', 'Long', 'Extra long'];

    public const HAIR_LENGTHS_MALE = ['Buzz cut', 'Short', 'Medium', 'Long'];

    public const HAIR_TEXTURES = ['Straight', 'Wavy', 'Curly', 'Coily'];

    public const EYE_COLORS = ['blue', 'green', 'brown', 'hazel', 'dark', 'light grey'];

    public const BUILDS_FEMALE = ['Petite', 'Slim', 'Athletic', 'Average', 'Curvy', 'Tall', 'Plus'];

    public const BUILDS_MALE = ['Slim', 'Athletic', 'Average', 'Muscular', 'Stocky', 'Tall'];

    public const ETHNICITIES = [
        'White', 'Black', 'Hispanic', 'East Asian', 'South Asian', 'Middle Eastern', 'Southeast Asian', 'Mixed',
    ];

    public const POSES = ['frontfacing', 'contemplative', 'plandid', 'posed_cute', 'candid'];

    public const ASPECT_RATIOS = ['9:16', '16:9'];
}
