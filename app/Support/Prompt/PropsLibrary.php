<?php

namespace App\Support\Prompt;

/**
 * Hand-held props candidates for prompt scenes. Null entries represent
 * "no prop"; drink props are excluded from candid variations.
 */
final class PropsLibrary
{
    public const PROPS = [
        'iced matcha latte in a clear to-go cup, bright green, paper straw, slight condensation',
        'iced coffee in a clear coffee shop cup with a dome lid, paper straw',
        'iced latte in a clear cup, light brown, ice visible through the sides, paper straw',
        'stainless steel wide-mouth water bottle, no logo, condensation on the outside',
        'small paper shopping bag held loosely at the side by the handles',
        'small pebbled leather tote held at the crook of the arm',
        'pair of clean sunglasses held loosely in one hand at the side',
        'worn-in paperback, held loosely by the spine',
        'small bouquet of dried or fresh flowers, stems in one hand',
        'small wired earbuds just removed, held loosely in one hand',
        'thin notebook held loosely under one arm',
        'phone held loosely at the side, screen off',
        null,
        null,
        null,
    ];

    public const DRINK_PATTERN = '/latte|matcha|coffee|tea|smoothie|cup|straw|thermos|bubble/';
}
