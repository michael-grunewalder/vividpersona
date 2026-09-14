<?php

namespace App\Support\Prompt;

/**
 * Aesthetic vibe word definitions: the defining tag used to hard-filter the
 * wardrobe pool, the tag family per vibe, and the colour palette hint injected
 * into the Wardrobe section of a prompt.
 */
final class VibeLibrary
{
    public const PRIMARY_TAG = [
        'Minimalist' => 'minimalist',
        'Editorial' => 'editorial',
        'Streetwear' => 'street',
        'Bohemian' => 'bohemian',
        'Glam' => 'glam',
        'Sporty' => 'sport',
        'Y2K' => 'y2k',
        'Dark & Moody' => 'dark',
        'Clean Girl' => 'clean',
        'Cottagecore' => 'cottagecore',
        'Tech Bro' => 'minimalist',
        'Preppy' => 'preppy',
        'Old Money' => 'old-money',
        'Coastal' => 'coastal',
    ];

    public const TAG_MAP = [
        'Minimalist' => ['minimalist', 'quiet', 'clean'],
        'Editorial' => ['editorial', 'structured', 'bold'],
        'Streetwear' => ['street', 'urban', 'casual'],
        'Bohemian' => ['bohemian', 'earthy', 'natural'],
        'Glam' => ['glam', 'evening', 'bold'],
        'Sporty' => ['sport', 'functional', 'casual'],
        'Y2K' => ['y2k', 'playful', 'casual'],
        'Dark & Moody' => ['dark', 'moody', 'structured'],
        'Clean Girl' => ['clean', 'natural', 'casual'],
        'Cottagecore' => ['cottagecore', 'natural', 'earthy'],
        'Tech Bro' => ['structured', 'quiet', 'minimalist'],
        'Preppy' => ['preppy', 'classic', 'polished'],
        'Old Money' => ['old-money', 'classic', 'polished', 'quiet'],
        'Coastal' => ['coastal', 'natural', 'casual'],
    ];

    public const PALETTES = [
        'Minimalist' => 'Palette: muted neutrals — off-white, ecru, stone, warm grey, oat. No strong saturated colors.',
        'Editorial' => 'Palette: bold monochrome or one statement color — all-black, deep charcoal, pure white, or a single saturated hue.',
        'Streetwear' => 'Palette: washed-out neutrals — faded black, washed grey, dirty white, faded indigo denim.',
        'Bohemian' => 'Palette: warm earthy tones — terracotta, rust, warm ochre, tobacco, deep olive, sun-faded warm neutrals.',
        'Glam' => 'Palette: rich evening tones — deep burgundy, warm ivory, champagne, or a bold jewel-tone statement piece.',
        'Sporty' => 'Palette: clean athletic colors — sage green, lavender, warm rust, classic black-and-white, or a pastel matching set.',
        'Y2K' => 'Palette: 2000s nostalgia — baby blue, lilac, pale pink, warm denim wash, silver hardware, white.',
        'Dark & Moody' => 'Palette: deep darks only — all-black, deep charcoal, dark forest, inky navy. No light or pastel tones.',
        'Clean Girl' => 'Palette: clean warm neutrals — warm white, oat, ecru, nude blush. Nothing saturated or dark.',
        'Cottagecore' => 'Palette: soft botanical — dusty rose, sage, warm cream, muted floral prints, soft lavender.',
        'Tech Bro' => 'Palette: monochrome and minimal — all-black, deep charcoal, dark navy, all-grey. No bright or warm tones.',
        'Preppy' => 'Palette: classic collegiate — navy and white, hunter green, camel and cream, burgundy.',
        'Old Money' => 'Palette: quiet luxury — camel, cream, stone, pale blue, navy, warm tan. Nothing loud or trend-driven.',
        'Coastal' => 'Palette: sun-bleached coastal — off-white linen, warm sand, ocean-washed denim, natural rope and shell tones.',
    ];
}
