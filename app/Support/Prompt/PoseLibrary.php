<?php

namespace App\Support\Prompt;

/**
 * Pose templates. Each pose has a `text` variant that interpolates a held prop
 * via the `{prop}` placeholder and an `alt` no-prop variant. The MAIN candid
 * pose is drink-aware: its `text` is the mid-sip variant, and a non-drink prop
 * resolves to the generic `alt` clause.
 */
final class PoseLibrary
{
    public const SOUL = [
        'facing' => [
            'text' => 'standing upright facing the camera, body straight and balanced, shoulders level, {prop} held loosely at the side in one hand — calm and simple, not leaning, not posed',
            'alt' => 'standing upright facing the camera, body straight and balanced, shoulders level, arms relaxed at the sides — calm and simple, not leaning, not posed',
        ],
        'angled' => [
            'text' => 'standing upright with the body turned slightly toward the camera, balanced and straight, {prop} held naturally in one hand at the side — simple and still, not leaning forward, not posed',
            'alt' => 'standing upright with the body turned slightly toward the camera, balanced and straight, shoulders relaxed, arms at sides — simple and still, not leaning forward, not posed',
        ],
        'candid' => [
            'text' => 'standing upright and looking toward the camera with a calm, natural expression, {prop} held loosely in one hand at the side — simple presence, not leaning, not mid-motion, not posed',
            'alt' => 'standing upright and looking toward the camera with a calm, natural expression, body balanced and still, arms relaxed — simple presence, not leaning, not mid-motion, not posed',
        ],
    ];

    public const MAIN = [
        'frontfacing' => [
            'text' => 'body facing directly toward camera, weight shifted onto one leg for a subtle hip tilt — relaxed, not stiff. {prop} held naturally in front of the body, cradled loosely in both hands at mid-chest. Eyes meeting the lens directly with a quiet, present expression — not forced, not a performance. The "comfortable in front of the camera" framing. Face fully visible and front-lit.',
            'alt' => 'body facing directly toward camera, weight shifted onto one leg for a subtle hip tilt — relaxed, not stiff. arms relaxed at the sides or one hand resting loosely near the hip, fingers natural. Eyes meeting the lens directly with a quiet, present expression — not forced, not a performance. The "comfortable in front of the camera" framing. Face fully visible and front-lit.',
        ],
        'contemplative' => [
            'text' => 'body facing 45° away from camera, weight forward on one leg. {prop} held loosely at the side, almost forgotten. Head turned back toward the lens mid-thought, eyes glancing toward but not fully meeting it — somewhere else mentally. A quiet, inward expression — not performing.',
            'alt' => 'body facing 45° away from camera, weight forward on one leg. hands relaxed loosely in front, fingers barely interlaced. Head turned back toward the lens mid-thought, eyes glancing toward but not fully meeting it — somewhere else mentally. A quiet, inward expression — not performing.',
        ],
        'plandid' => [
            'text' => 'body angled 25–30° to camera, weight settled on the back leg, hips slightly offset. {prop} held naturally in one hand, wrist relaxed. Eyes glancing down-and-off-axis, 15° away from lens. Expression caught mid-thought — a specific private moment. The "noticed the camera half a second ago" framing.',
            'alt' => 'body angled 25–30° to camera, weight settled on the back leg, hips slightly offset. one hand mid-loose-gesture near the hip, the other hanging naturally. Eyes glancing down-and-off-axis, 15° away from lens. Expression caught mid-thought — a specific private moment. The "noticed the camera half a second ago" framing.',
        ],
        'posed_cute' => [
            'text' => 'body in soft 3/4 angle to camera, shoulders relaxed and slightly dropped. {prop} held in both hands at chest height, elbows soft. Eyes meeting the lens with a quiet small expression — a half-smile just forming, not fully committed. Posing but acting like she isn\'t.',
            'alt' => 'body in soft 3/4 angle to camera, shoulders relaxed and slightly dropped. one hand gently touching the side of the jaw, fingers loose and natural. Eyes meeting the lens with a quiet small expression — a half-smile just forming, not fully committed. Posing but acting like she isn\'t.',
        ],
        'candid' => [
            'text' => 'mid-action — caught at the apex of bringing the {prop} toward the mouth, mid-sip, body naturally leaning slightly forward. Eyes looking directly toward the lens — spontaneous, unguarded eye contact full of real energy. Not posed, not looking away — the camera caught them in a real moment while they were already looking at it.',
            'alt' => 'mid-action — caught at the apex of a genuine mid-laugh or bright spontaneous expression, body and shoulders caught in motion, one hand mid-gesture near the chest. Eyes looking directly toward the lens — spontaneous, unguarded eye contact full of real energy. Not posed, not looking away — the camera caught them in a real moment while they were already looking at it.',
        ],
    ];

    /**
     * Pick a MAIN pose key from the personality score, mirroring the source
     * thresholds and randomness.
     */
    public static function poseKeyFromPersonality(int $personality): string
    {
        if ($personality < 28) {
            return 'contemplative';
        }

        if ($personality < 50) {
            return 'plandid';
        }

        if ($personality < 70) {
            return mt_rand() > (mt_getrandmax() / 2) ? 'plandid' : 'posed_cute';
        }

        return mt_rand() > (mt_getrandmax() / 2) ? 'candid' : 'posed_cute';
    }

    /**
     * Return one of the MAIN poses selected from the personality score.
     *
     * @return array{text: string, alt: string}
     */
    public static function poseFromPersonality(int $personality): array
    {
        return self::MAIN[self::poseKeyFromPersonality($personality)];
    }
}
