<?php

namespace App\Support\Prompt;

/**
 * Builds iPhone-realism persona photo prompts from a profile array,
 * ported from the React reference app's systemPrompt.js and Create.jsx.
 */
final class PersonaPromptBuilder
{
    private const OUTDOOR_SCENE_PATTERN = '/\b(park|trail|beach|rooftop|street|pavement|outdoor|alley|plaza|market|terrace|harbor|path|square|city|urban|cobblestone|courtyard|sidewalk|promenade)\b/i';

    private const FACIAL_SHAPES = [
        'soft round face with full cheeks',
        'oval face with balanced proportions',
        'heart-shaped face with a softly tapered chin',
        'square jaw with strong defined bone structure',
        'angular face with high, sharp cheekbones',
        'long narrow face with delicate bone structure',
    ];

    private const FACIAL_BROWS = [
        'straight, softly arched brows',
        'high, well-defined brows',
        'low, straight brows close to the eyes',
        'feathered, lightly-groomed brows',
    ];

    private const FACIAL_MARKERS = [
        'a small beauty mark near the mouth',
        'a light dusting of freckles across the nose and cheeks',
        'a subtle dimple on one cheek',
        'slightly prominent, expressive eyes',
        'full, naturally defined lips',
        'a faint old scar on the brow',
    ];

    /**
     * Build the physical description string from the profile fields.
     */
    public static function physicalDesc(array $d): string
    {
        $parts = [];

        if (! empty($d['ethnicity'])) {
            $parts[] = strtolower((string) $d['ethnicity']);
        }

        $hairParts = array_values(array_filter(
            [$d['hairLength'] ?? null, $d['hairTexture'] ?? null, $d['hairColor'] ?? null],
            static fn ($value): bool => ! empty($value),
        ));
        $hairParts = array_map(static fn ($value): string => strtolower((string) $value), $hairParts);

        if ($hairParts !== []) {
            $parts[] = implode(' ', $hairParts).' hair';
        }

        if (! empty($d['eyeColor'])) {
            $parts[] = strtolower((string) $d['eyeColor']).' eyes';
        }

        if (! empty($d['skinTone'])) {
            $parts[] = strtolower((string) $d['skinTone']).' skin tone';
        }

        if (! empty($d['build'])) {
            $parts[] = strtolower((string) $d['build']).' build';
        }

        if (! empty(trim((string) ($d['uniqueFeatures'] ?? '')))) {
            $parts[] = trim((string) $d['uniqueFeatures']);
        }

        return implode(', ', $parts);
    }

    /**
     * Build three distinct variation prompts: variation 1 = frontfacing,
     * 2 = posed_cute, 3 = candid (outdoor or backstory-locked). Soul models
     * use the simplified soul-safe pose set instead.
     *
     * @return array<int, string>
     */
    public static function buildThreeVariationPrompts(array $d, string $aspectRatio = '9:16', string $model = 'default'): array
    {
        $drinkUsed = false;

        $sessionProp = static function (bool $noDrink = false) use (&$drinkUsed): ?string {
            $prop = self::getProp($noDrink || $drinkUsed);

            if (self::isDrinkProp($prop)) {
                $drinkUsed = true;
            }

            return $prop;
        };

        $identities = self::facialIdentities(3);

        $withIdentity = static fn (int $i, array $profile): array => array_merge($profile, ['facialDetail' => $identities[$i]]);

        if (self::isSoulModel($model)) {
            return [
                self::buildDirectPrompt($withIdentity(0, $d), 'facing', ['model' => $model, 'forceProp' => $sessionProp()], $aspectRatio),
                self::buildDirectPrompt($withIdentity(1, $d), 'angled', ['model' => $model, 'forceProp' => $sessionProp()], $aspectRatio),
                self::buildDirectPrompt($withIdentity(2, $d), 'candid', ['forceOutdoor' => true, 'model' => $model, 'forceProp' => $sessionProp(true)], $aspectRatio),
            ];
        }

        $tier3Ctx = self::getBackstoryContext(self::physicalDesc($d), trim((string) ($d['backstory'] ?? '')));
        $hasBackstoryLock = $tier3Ctx['sceneNiche'] !== null
            || $tier3Ctx['lockedScene'] !== null
            || ! empty($d['backstoryContext']['sceneNiche']);

        return [
            self::buildDirectPrompt($withIdentity(0, $d), 'frontfacing', ['forceProp' => $sessionProp()], $aspectRatio),
            self::buildDirectPrompt($withIdentity(1, $d), 'posed_cute', ['forceProp' => $sessionProp()], $aspectRatio),
            $hasBackstoryLock
                ? self::buildDirectPrompt($withIdentity(2, $d), 'candid', ['backstoryLocked' => true, 'forceProp' => null], $aspectRatio)
                : self::buildDirectPrompt($withIdentity(2, $d), 'candid', ['forceOutdoor' => true, 'forceProp' => $sessionProp(true)], $aspectRatio),
        ];
    }

    /**
     * Build a single direct prompt from a profile. The pose key selects one of
     * the MAIN (or SOUL) poses; an empty key derives one from the personality
     * score. Recognised options: backstoryLocked, forceOutdoor, model, forceProp.
     */
    public static function buildDirectPrompt(array $d, string $poseKey = '', array $options = [], string $aspectRatio = '9:16'): string
    {
        $gender = $d['gender'] ?? 'woman';
        $age = ! empty($d['age']) ? $d['age'].' year old' : 'mid-20s';
        $physicalDesc = self::physicalDesc($d);
        $physical = trim($physicalDesc) !== '' ? $physicalDesc : 'with dark hair, warm complexion, natural features';
        $vibes = $d['vibeWords'] ?? [];
        $personality = (int) ($d['personality'] ?? 50);
        $backstory = trim((string) ($d['backstory'] ?? ''));
        $isEditorial = in_array('Editorial', $vibes, true);

        $tier3Ctx = self::getBackstoryContext($physicalDesc, $backstory);
        $backstoryCtx = self::mergeBackstoryContext($tier3Ctx, $d['backstoryContext'] ?? null);
        $sceneNiche = $backstoryCtx['sceneNiche'];
        $buildHint = $backstoryCtx['buildHint'];
        $physicalDetail = $backstoryCtx['physicalDetail'];
        $lockedScene = $backstoryCtx['lockedScene'];

        $niches = $d['niches'] ?? [];

        if ($niches === [] && ! empty($d['niche'])) {
            $niches = array_values(array_filter(array_map('trim', explode(',', (string) $d['niche']))));
        }

        if (! empty($options['backstoryLocked'])) {
            if ($lockedScene !== null) {
                $scene = $lockedScene;
            } elseif ($sceneNiche !== null && isset(SceneLibrary::POOLS[$sceneNiche])) {
                $scene = self::random(SceneLibrary::POOLS[$sceneNiche]);
            } else {
                $scene = self::random(self::scenePool($niches));
            }
        } elseif (! empty($options['forceOutdoor'])) {
            $scene = self::random(SceneLibrary::OUTDOOR_CANDID);
        } elseif ($sceneNiche !== null && isset(SceneLibrary::POOLS[$sceneNiche])) {
            $scene = self::random(SceneLibrary::POOLS[$sceneNiche]);
        } else {
            $scene = self::random(self::scenePool($niches));
        }

        $timeConfig = self::random(preg_match(self::OUTDOOR_SCENE_PATTERN, $scene) === 1 ? self::outdoorTimes() : TimeLibrary::TIMES);

        $poseKey = $poseKey !== '' ? $poseKey : PoseLibrary::poseKeyFromPersonality($personality);
        $isSoul = self::isSoulModel($options['model'] ?? 'default');
        $prop = array_key_exists('forceProp', $options) ? $options['forceProp'] : self::getProp();

        $forcedProfTags = (! empty($options['backstoryLocked']) && $vibes === [])
            ? ($lockedScene !== null
                ? array_values(array_filter($backstoryCtx['tags'], static fn (string $tag): bool => ! in_array($tag, ['casual', 'urban', 'street'], true)))
                : $backstoryCtx['tags'])
            : null;

        $wardrobeBase = self::selectWardrobe($d, $forcedProfTags);

        $paletteLine = $isSoul
            ? ''
            : implode(' | ', array_values(array_filter(
                array_map(static fn (string $vibe): ?string => VibeLibrary::PALETTES[$vibe] ?? null, $vibes),
            )));

        $wardrobe = $paletteLine !== '' ? $wardrobeBase."\n".$paletteLine : $wardrobeBase;

        $camera = self::camera();
        $skinBlock = self::skinBlock($timeConfig['label'], $gender, $physical);
        $characterFraming = self::characterFraming($personality);

        $poseText = self::renderPoseText($poseKey, $prop, $isSoul);
        $propDesc = $prop !== null
            ? $prop.' held in one hand — no visible brand logo'
            : 'hands in a natural mid-gesture, nothing held';

        $buildDesc = (! empty($options['backstoryLocked']) && $buildHint !== null && trim((string) ($d['build'] ?? '')) === '')
            ? ', '.$buildHint
            : '';
        $physicalDetailStr = (! empty($options['backstoryLocked']) && $physicalDetail !== null)
            ? ', '.$physicalDetail
            : '';

        $facialDetailStr = (empty($d['faceRef'] ?? null) && filled($d['facialDetail'] ?? null))
            ? ', with '.$d['facialDetail']
            : '';

        $poseName = match ($poseKey) {
            'frontfacing' => 'iPhone portrait — direct, relaxed, facing camera',
            'contemplative' => 'iPhone portrait — quiet, present, facing camera',
            'plandid' => 'iPhone candid — soft awareness, facing camera',
            'posed_cute' => 'iPhone feed shot — soft pose, eyes at lens',
            default => 'iPhone candid — mid-moment, eyes at the lens',
        };

        $sceneLine = (! empty($options['backstoryLocked']) && $lockedScene !== null)
            ? $scene
            : $scene.', '.$timeConfig['label'];

        $aspectText = $aspectRatio === '16:9'
            ? 'Horizontal landscape frame — subject standing close to camera, filling at least half the frame height, environment visible on both sides. Not a distant wide shot — the subject must be close enough that face and outfit detail are fully legible. This is a wide candid, not a portrait crop rotated sideways.'
            : 'Subject fills 60–70% of the 9:16 frame — tight crop, not a wide environmental shot.';

        $editorialSuffix = $isEditorial
            ? ' Editorial vibe applies to the styling only — the photo itself is a raw iPhone snapshot.'
            : '';

        return "Photograph style: iPhone 16 Pro snapshot. Taken by the subject or a nearby friend, handheld, automatic settings. No professional crew, no studio, no lighting setup, no direction given. Raw iPhone output — unedited. The subject is unaware this will be published — a personal photo, not intended for any shoot.

Scene: {$sceneLine}. Empty of other people. If the location is an interior, it shows real signs of habitation — not a styled showroom. Background is real, in-focus, and unmanipulated exactly as an iPhone captures it — no blur, no bokeh, no artificial depth of field. The subject is the hero through tight framing and natural lighting, not through background manipulation.

Subject: {$gender}, {$age}, {$physical}{$buildDesc}{$physicalDetailStr}{$facialDetailStr}. {$characterFraming}. Natural micro-asymmetries in the face — this is a real iPhone photograph of a real person, not a 3D render or CGI. Real visible pore texture on the nose, cheeks, and forehead — and on all exposed skin including arms, neck, and shoulders. Zero skin smoothing anywhere on the body, zero airbrushing, zero beauty filter applied.

Pose: {$poseText} {$propDesc}.

Wardrobe & details: {$wardrobe}

Lighting: {$timeConfig['lighting']} The subject's face is the brightest element in the frame. This is natural found light — not a lighting setup.

Camera & capture: {$camera}.

Skin (rendered as concrete photographic facts, not category words):
{$skinBlock}

Use case: {$poseName}

Constraints: no people in the background. No visible brand logos on any item. {$aspectText} No background blur or bokeh. Real pore texture and skin imperfections visible on the face and all exposed body skin — zero beauty retouching. No AI aesthetic markers: no unnaturally bright irises, no perfectly symmetrical face, no plastic-smooth skin, no uncanny glow. No phone screen, no social media UI, no app overlay, no notification bar, no status bar, no interface elements of any kind visible anywhere in the image. This is a raw photograph — no digital overlays, no framing devices, no UI chrome.{$editorialSuffix}";
    }

    /**
     * Select a wardrobe entry from the profile, scoring entries by energy
     * proximity to the personality score and vibe tag overlap.
     *
     * @param  bool|array<int, string>|null  $forceProfessionTags
     */
    public static function selectWardrobe(array $d, bool|array|null $forceProfessionTags = false): string
    {
        $gender = $d['gender'] ?? 'female';
        $isMale = strtolower((string) $gender) === 'male';
        $vibeWords = $d['vibeWords'] ?? [];
        $personality = (int) ($d['personality'] ?? 50);
        $context = self::getBackstoryContext(self::physicalDesc($d), trim((string) ($d['backstory'] ?? '')));

        $vibeTags = array_merge(self::getVibeTags($vibeWords), $context['tags']);

        if (is_array($forceProfessionTags)) {
            $primaryVibeTags = $forceProfessionTags;
        } elseif ($forceProfessionTags === true) {
            $primaryVibeTags = $context['tags'];
        } else {
            $primaryVibeTags = [];
        }

        if ($primaryVibeTags === [] && $vibeWords !== []) {
            foreach ($vibeWords as $vibe) {
                $primaryTag = VibeLibrary::PRIMARY_TAG[$vibe] ?? null;

                if ($primaryTag !== null) {
                    $primaryVibeTags[] = $primaryTag;
                }
            }

            $primaryVibeTags = array_values(array_unique($primaryVibeTags));
        }

        $genderPool = array_values(array_filter(
            WardrobeLibrary::ENTRIES,
            static fn (array $entry): bool => $entry['gender'] === ($isMale ? 'male' : 'female'),
        ));

        if ($primaryVibeTags !== []) {
            $filtered = array_values(array_filter(
                $genderPool,
                static fn (array $entry): bool => array_intersect($entry['tags'], $primaryVibeTags) !== [],
            ));

            $pool = count($filtered) >= 3 ? $filtered : $genderPool;
        } else {
            $pool = $genderPool;
        }

        $scored = [];

        foreach ($pool as $entry) {
            $score = 0;

            $energyDist = abs($entry['energy'] - $personality);

            if ($energyDist <= 15) {
                $score += 40;
            } elseif ($energyDist <= 28) {
                $score += 20;
            } else {
                $score -= 20;
            }

            if ($vibeTags !== []) {
                $score += count(array_intersect($entry['tags'], $vibeTags)) * 18;
            }

            $score += mt_rand() / mt_getrandmax() * 14;

            $scored[] = ['entry' => $entry, 'score' => $score];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $topN = min(6, count($scored));
        $chosen = array_slice($scored, 0, $topN);
        $entry = self::random($chosen)['entry'];

        return $entry['text'].'. '.self::getStylingNote($personality);
    }

    /**
     * Return the styling note describing how the outfit is worn.
     */
    public static function getStylingNote(int $personality): string
    {
        if ($personality < 25) {
            return 'Worn without deliberateness — clothes exist, not styled.';
        }

        if ($personality < 45) {
            return 'Put together but not overthought — one considered choice, the rest just fits.';
        }

        if ($personality < 60) {
            return 'Casually considered — looks good without looking like effort.';
        }

        if ($personality < 78) {
            return 'Visibly intentional — pulling the look together on purpose, naturally.';
        }

        return 'Fully committed — every piece deliberate, confident in the choices.';
    }

    /**
     * Aggregate wardrobe tags, scene niche, build hint, physical detail and
     * locked scene from every backstory archetype that matches the text.
     *
     * @return array{tags: array<int, string>, sceneNiche: string|null, buildHint: string|null, physicalDetail: string|null, lockedScene: string|null}|null
     */
    public static function getBackstoryContext(string $physicalDesc, string $backstory): ?array
    {
        $text = strtolower(trim($physicalDesc).' '.trim($backstory));

        $tags = [];
        $sceneNiche = null;
        $buildHint = null;
        $physicalDetail = null;
        $lockedScene = null;

        foreach (BackstoryArchetypes::ENTRIES as $archetype) {
            if (@preg_match($archetype['test'], $text)) {
                foreach ($archetype['tags'] as $tag) {
                    if (! in_array($tag, $tags, true)) {
                        $tags[] = $tag;
                    }
                }

                if ($sceneNiche === null && $archetype['sceneNiche'] !== null) {
                    $sceneNiche = $archetype['sceneNiche'];
                }

                if ($buildHint === null && $archetype['buildHint'] !== null) {
                    $buildHint = $archetype['buildHint'];
                }

                if ($physicalDetail === null && $archetype['physicalDetail'] !== null) {
                    $physicalDetail = $archetype['physicalDetail'];
                }

                if ($lockedScene === null && $archetype['lockedScene'] !== null) {
                    $lockedScene = $archetype['lockedScene'];
                }
            }
        }

        return [
            'tags' => $tags,
            'sceneNiche' => $sceneNiche,
            'buildHint' => $buildHint,
            'physicalDetail' => $physicalDetail,
            'lockedScene' => $lockedScene,
        ];
    }

    /**
     * Merge an optional caller-supplied backstory context over the tier-3
     * archetype context, mirroring the reference app's backstoryContext merge.
     *
     * @param  array<string, mixed>|null  $override
     * @return array<string, mixed>
     */
    private static function mergeBackstoryContext(array $tier3Ctx, ?array $override): array
    {
        if ($override === null) {
            return $tier3Ctx;
        }

        return [
            'buildHint' => $tier3Ctx['buildHint'],
            'physicalDetail' => $tier3Ctx['physicalDetail'],
            'lockedScene' => $tier3Ctx['lockedScene'],
            'tags' => ! empty($override['tags']) ? $override['tags'] : $tier3Ctx['tags'],
            'sceneNiche' => $override['sceneNiche'] ?? $tier3Ctx['sceneNiche'],
            'dailyContext' => $override['dailyContext'] ?? null,
        ];
    }

    /**
     * @param  array<int, string>  $vibeWords
     * @return array<int, string>
     */
    private static function getVibeTags(array $vibeWords): array
    {
        $tags = [];

        foreach ($vibeWords as $vibe) {
            foreach (VibeLibrary::TAG_MAP[$vibe] ?? [] as $tag) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    /**
     * Resolve the scene pool for the given niches, falling back to the union
     * of every pool when nothing maps.
     *
     * @param  array<int, string>  $niches
     * @return array<int, string>
     */
    private static function scenePool(array $niches): array
    {
        if ($niches === []) {
            return self::allScenes();
        }

        $keys = [];

        foreach ($niches as $niche) {
            $key = SceneLibrary::NICHE_KEY[strtolower((string) $niche)] ?? null;

            if ($key !== null) {
                $keys[$key] = true;
            }
        }

        if ($keys === []) {
            return self::allScenes();
        }

        $pool = [];

        foreach (array_keys($keys) as $key) {
            foreach (SceneLibrary::POOLS[$key] ?? [] as $scene) {
                $pool[] = $scene;
            }
        }

        return $pool !== [] ? $pool : self::allScenes();
    }

    /**
     * @return array<int, string>
     */
    private static function allScenes(): array
    {
        $scenes = [];

        foreach (SceneLibrary::POOLS as $pool) {
            foreach ($pool as $scene) {
                $scenes[] = $scene;
            }
        }

        return $scenes;
    }

    /**
     * Time configs compatible with outdoor scenes (no indoor or café labels).
     *
     * @return array<int, array{label: string, lighting: string}>
     */
    private static function outdoorTimes(): array
    {
        return array_values(array_filter(
            TimeLibrary::TIMES,
            static fn (array $time): bool => ! str_contains($time['label'], 'indoor') && ! str_contains($time['label'], 'café'),
        ));
    }

    /**
     * Render a pose template, interpolating the prop placeholder or falling
     * back to the no-prop alt text. The MAIN candid pose is drink-aware.
     */
    private static function renderPoseText(string $poseKey, ?string $prop, bool $isSoul): string
    {
        $pose = $isSoul
            ? (PoseLibrary::SOUL[$poseKey] ?? PoseLibrary::SOUL['facing'])
            : (PoseLibrary::MAIN[$poseKey] ?? PoseLibrary::MAIN['frontfacing']);

        if ($prop === null) {
            return $pose['alt'];
        }

        if ($poseKey === 'candid' && ! $isSoul) {
            return self::isDrinkProp($prop)
                ? str_replace('{prop}', $prop, $pose['text'])
                : $pose['alt'];
        }

        return str_replace('{prop}', $prop, $pose['text']);
    }

    /**
     * Generate a hand prop. A drink is excluded when noDrink is set, mirroring
     * the reference app's ~70% prop gate with randomness.
     */
    private static function getProp(bool $noDrink = false): ?string
    {
        if (mt_rand(1, 100) > 70) {
            return null;
        }

        $options = $noDrink
            ? array_values(array_filter(PropsLibrary::PROPS, static fn (?string $prop): bool => $prop !== null && ! self::isDrinkProp($prop)))
            : PropsLibrary::PROPS;

        return self::random($options);
    }

    private static function isDrinkProp(?string $prop): bool
    {
        return $prop !== null && preg_match(PropsLibrary::DRINK_PATTERN, $prop) === 1;
    }

    private static function isSoulModel(string $model): bool
    {
        return in_array($model, ['soul_2', 'soul'], true);
    }

    private static function camera(): string
    {
        return 'iPhone 16 Pro 24mm main lens f/1.78, held at arm\'s length or by a nearby friend, automatic exposure, natural sensor noise in shadow areas, slight lens barrel distortion at edges, 9:16 vertical, chest-up framing, face at the upper-third line, subject fills the center of the frame';
    }

    private static function characterFraming(int $personality): string
    {
        if ($personality < 30) {
            return 'quiet, inward energy — someone with a rich internal world, not performing for the camera. Real person, real life, not a model on a shoot';
        }

        if ($personality > 70) {
            return 'open, present energy — moves through the world with ease, comfortable being seen. Real person, real life, not a model on a shoot';
        }

        return 'natural, unhurried energy — comfortable in the moment. Real person, real life, not a model on a shoot';
    }

    /**
     * Build the skin realism block for the Lighting section of a prompt.
     */
    private static function skinBlock(string $timeLabel, string $gender, string $physicalDesc): string
    {
        $pronoun = strtolower($gender) === 'male' ? 'his' : 'her';
        $p = strtolower($physicalDesc);
        $hasFair = str_contains($p, 'fair') || str_contains($p, 'pale') || str_contains($p, 'light skin');
        $hasDark = str_contains($p, 'dark skin') || str_contains($p, 'deep') || str_contains($p, 'ebony') || str_contains($p, 'melanin');

        $envReactions = [
            'golden hour late afternoon' => $hasFair
                ? 'warm golden flush across the forehead and cheekbone tops — more visible on fair skin, the lit side noticeably warm'
                : ($hasDark
                    ? 'rich deep skin tones dimensional in the golden light, the high points — forehead, cheekbone — catching warmth'
                    : 'faint sun-warmth across the forehead and tops of the cheekbones from afternoon outdoor exposure'),
            'overcast soft daylight' => 'even, cool-environment skin with a natural healthy flush — no sun warmth, no windburn',
            'bright sunny mid-morning' => 'slight sun-warmth flush on the forehead and nose bridge, micro-fine sweat at the temples catching the hard light',
            'blue-hour dusk' => 'faint cold-air redness around the nostrils and upper cheeks from the cooling evening temperature',
            'soft indoor afternoon window' => 'soft thermal indoor flush at the cheeks from the heated space, gentle warmth settling across the face',
            'morning indoor café' => 'a light warmth-flush as the skin adjusts from outdoor cool to the heated interior, slight color at the cheeks',
        ];

        $reaction = $envReactions[$timeLabel] ?? $envReactions['overcast soft daylight'];

        $imperfection = self::random([
            'a small healing blemish on the left jaw, slightly pinker than surrounding skin',
            'faint asymmetric sun pigmentation near the right temple',
            'two freckles placed asymmetrically across the nose and left cheek',
            'a faint old thin scar below the right jawline — barely there, photographically real',
            'slight horizontal pressure line across the forehead from a hat worn earlier',
        ]);

        return "— Visible individual pores across {$pronoun} T-zone, nose, and cheeks; pores on the lit side cast tiny directional micro-shadows from the key light
— {$reaction}
— {$imperfection}
— Left brow sits marginally higher than the right; one nostril slightly narrower; cupid's bow peaks uneven — natural asymmetry throughout
— Subtle digital sensor noise in the shadow areas consistent with iPhone auto-ISO";
    }

    /**
     * Build $count distinct facial identity clauses so the variation prompts
     * read as different people while sharing the same selected look. The pools
     * are rotated randomly per call so successive generation sets rarely
     * repeat a face.
     *
     * @return array<int, string>
     */
    private static function facialIdentities(int $count): array
    {
        $rotate = static function (array $arr): array {
            $offset = random_int(0, count($arr) - 1);

            return array_merge(array_slice($arr, $offset), array_slice($arr, 0, $offset));
        };

        $shapes = $rotate(self::FACIAL_SHAPES);
        $brows = $rotate(self::FACIAL_BROWS);
        $markers = $rotate(self::FACIAL_MARKERS);

        $identities = [];

        for ($i = 0; $i < $count; $i++) {
            $identities[] = $shapes[$i % count($shapes)].', '.$brows[$i % count($brows)].', and '.$markers[$i % count($markers)];
        }

        return $identities;
    }

    /**
     * Pick a random element from an array.
     */
    private static function random(array $array): mixed
    {
        if ($array === []) {
            return null;
        }

        return $array[mt_rand(0, count($array) - 1)];
    }
}
