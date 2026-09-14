<?php

namespace App\Support\Prompt;

/**
 * Time-of-day / lighting configurations available for prompt scenes.
 */
final class TimeLibrary
{
    public const TIMES = [
        [
            'label' => 'golden hour late afternoon',
            'lighting' => 'late afternoon sun hitting from behind-right — warm golden light rimming the hair and the lit cheek, open sky filling the shadow side in cooler bluish tones. Both temperatures visible on the face at the same time. The kind of accidental perfect light you get outside around 5pm.',
        ],
        [
            'label' => 'overcast soft daylight',
            'lighting' => 'solid overcast sky diffusing everything into even, directionless softness — no hard shadows anywhere, just gentle top-down modeling from the brightest patch of cloud. Cool and neutral throughout. Flat but honest.',
        ],
        [
            'label' => 'bright sunny mid-morning',
            'lighting' => 'direct mid-morning sun from the front-left — hard, honest daylight with a clear nose shadow and a bright lit side. The shadow side filled by open blue sky. Exactly the kind of full-sun look an iPhone captures with auto exposure outdoors.',
        ],
        [
            'label' => 'blue-hour dusk',
            'lighting' => 'sun already down — a warm sodium streetlamp from camera-left is the main light, directional and orange. The open sky fills the shadow side in deep cool blue. A nearby shopfront or neon sign catches the hair from behind. Three natural color sources on the face simultaneously, all because of where she is standing.',
        ],
        [
            'label' => 'soft indoor afternoon window',
            'lighting' => 'afternoon daylight coming through a large window to camera-left — the near cheek lit and warm, falling off across the shadow side. Warm overhead room lighting fills the shadows gently. Window light and ambient room fill present at the same time. Natural, found, not arranged.',
        ],
        [
            'label' => 'morning indoor café',
            'lighting' => 'window to the right letting in morning daylight as the main light source — warm overhead café bulbs secondary, faint cool screen or phone glow on the shadow side. Three natural light sources visible on the face because of where she\'s sitting, not because anything was set up.',
        ],
    ];
}
