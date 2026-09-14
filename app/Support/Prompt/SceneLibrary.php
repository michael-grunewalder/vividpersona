<?php

namespace App\Support\Prompt;

/**
 * Location scene pools keyed by niche, plus the niche→pool key mapping and the
 * outdoor-only scenes reserved for the candid variation.
 */
final class SceneLibrary
{
    public const NICHE_KEY = [
        'fashion' => 'fashion',
        'beauty' => 'beauty',
        'lifestyle' => 'lifestyle',
        'fitness' => 'fitness',
        'travel' => 'travel',
        'food & dining' => 'lifestyle',
        'food' => 'lifestyle',
        'tech' => 'tech',
        'gaming' => 'gaming',
        'finance' => 'tech',
        'entertainment' => 'entertainment',
        'wellness' => 'fitness',
        'sports' => 'fitness',
        'sport' => 'fitness',
    ];

    public const POOLS = [
        'fashion' => [
            'sun-drenched SoHo loft, whitewashed exposed brick, tall factory windows, morning light pooling on oak floors',
            'Paris Le Marais narrow side street, charcoal limestone facades, cobblestones faintly damp, warm afternoon shade with a bright lane-end opening behind',
            'minimal Copenhagen café interior, raw concrete wall, warm oak counter surface catching window light',
            'Tokyo backstreet alley, clean concrete with layers of soft-focus weathered signage, warm evening ambient',
            'Milanese internal courtyard, faded terracotta plaster walls, afternoon sun cutting a diagonal stripe across the floor',
        ],
        'beauty' => [
            'sun-flooded modern bathroom, small window at 9 o\'clock, white subway tiles, faint steam in the air, morning',
            'clean neutral hotel room, floor-to-ceiling window, soft afternoon city skyline behind',
            'bedroom with large east-facing window, morning light angled low, white walls',
            'minimalist vanity corner, large mirror catching daylight from a nearby window',
        ],
        'fitness' => [
            'minimalist gym, natural oak floors, white walls, high windows with golden side-light from camera-left',
            'outdoor park trail, city skyline softened beyond treeline, morning ground mist',
            'concrete rooftop workout space, city spread below, blue-hour transition light',
            'empty beach at low tide, wet packed sand, low warm morning light catching the surface',
            'urban outdoor running path, tree canopy overhead, soft dappled morning light through leaves',
        ],
        'travel' => [
            'terracotta European alleyway, afternoon shade, warm stone walls both sides, bright sunlit opening far behind',
            'elevated viewpoint, city in golden hour atmospheric haze below',
            'harbor wall of a small coastal town, boats soft in background, overcast morning',
            'narrow Kyoto side street, wooden facades, soft diffused late-afternoon light',
            'rooftop above Mediterranean rooflines, warm evening, terracotta and white receding behind',
        ],
        'lifestyle' => [
            'warm neighborhood café corner, marble counter, condensation on the window, mid-afternoon light',
            'sun-bleached urban rooftop terrace, city rooflines behind, golden hour',
            'city pavement under a canopy of plane trees, mottled shade-and-sun, late morning',
            'hotel room window ledge, soft city panorama through glass, afternoon',
            'sun-filled apartment kitchen, white tiles, indoor plants catching window light, morning',
        ],
        'tech' => [
            'minimal open-plan office, floor-to-ceiling window, cool diffused cloud daylight, industrial ceiling soft behind',
            'coffee shop corner, laptop implied just off-frame, morning window light from 9 o\'clock',
            'clean home studio setup, soft north-facing window, minimal desk behind',
            'modern co-working space, city view through full-height glass, afternoon',
        ],
        'gaming' => [
            'clean battlestation visible behind, RGB soft-glow as background, one window at 90° as key light',
            'modern space, neon accent soft in background, late evening one-window key',
        ],
        'entertainment' => [
            'rooftop at golden hour, city below, warm haze',
            'urban mural wall, bold color out of focus, late afternoon',
            'backstage corridor, warm strip practicals above, evening',
        ],
        'default' => [
            'clean urban street corner, warm afternoon, city architecture as soft atmospheric background',
            'bright minimal interior, natural daylight from large windows, white walls receding',
            'rooftop with city skyline behind, golden hour haze, warm atmospheric depth',
            'café window seat, street softened behind glass, afternoon light from the left',
        ],
    ];

    public const OUTDOOR_CANDID = [
        'tree-lined city sidewalk, mid-morning, soft dappled light filtering through the canopy overhead',
        'outdoor café terrace, small round table nearby, city street softened behind, mid-afternoon sun',
        'sunny boutique shopping street, warm afternoon light on the storefronts, no other people',
        'urban park path, greenery on both sides, natural soft mid-morning light through the trees',
        'corner outside a coffee shop, city intersection behind, warm mid-morning light from the side',
        'sun-warmed city steps or a low ledge at the edge of an open plaza, afternoon light from the front',
        'wide sun-drenched pavement outside a row of boutiques, warm late afternoon',
        'rooftop terrace with a few potted plants, city rooflines visible behind, golden hour light',
        'narrow sunny side street, warm afternoon light cutting diagonally across the pavement',
        'small outdoor market square, light warm and directional, the square otherwise empty',
    ];
}
