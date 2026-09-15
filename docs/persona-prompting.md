# Persona prompting system

The persona avatar prompts are built in PHP under `app/Support/Prompt/`. They
are a faithful port of the reference app's `systemPrompt.js`, so the generated
prompts follow a fixed 10-section structure and draw from a large wardrobe /
scene / archetype library.

## The prompt structure

Every prompt (`PersonaPromptBuilder::buildDirectPrompt`) has 10 sections:

1. `Photograph style:` — iPhone 16 Pro snapshot framing.
2. `Scene:` — picked from the backstory `lockedScene`, the niche scene pool, or the outdoor candid scenes; time-of-day appended.
3. `Subject:` — `gender, age, physical description` + backstory build hint / physical detail + personality framing.
4. `Pose:` — personality-driven pose text + an optional prop.
5. `Wardrobe & details:` — `selectWardrobe` output + styling note + vibe palette hint.
6. `Lighting:` — from `TimeLibrary`, "face is the brightest element".
7. `Camera & capture:` — iPhone 16 Pro, 24mm f/1.78, framing.
8. `Skin:` — porosity, environmental reaction, one blemish, asymmetry, sensor noise.
9. `Use case:` — label for the chosen pose.
10. `Constraints:` — no people/logos/UI, aspect framing, no AI markers.

`buildThreeVariationPrompts` returns **3 different prompts** (frontfacing /
posed_cute / candid for the default model; facing / angled / candid for the
`soul` model), each with a different pose, scene and wardrobe — so the 3
generated avatars are distinct looks, not near-identical faces.

## Files

| File | Contents |
| --- | --- |
| `PersonaPromptBuilder.php` | `physicalDesc()`, `buildThreeVariationPrompts()`, `buildDirectPrompt()`, `selectWardrobe()`, `getStylingNote()`, `getBackstoryContext()` |
| `WardrobeLibrary.php` | 119 wardrobe entries |
| `VibeLibrary.php` | vibe → primary tag, tag map, palette hints |
| `SceneLibrary.php` | niche → scene pools, outdoor candid scenes |
| `TimeLibrary.php` | lighting/time configurations |
| `PoseLibrary.php` | pose templates + `poseFromPersonality()` |
| `PropsLibrary.php` | universal props + drink pattern |
| `BackstoryArchetypes.php` | 100 backstory archetypes (regex → tags/scene/build hint/detail) |

## How to extend

### Add a wardrobe outfit
Open `WardrobeLibrary.php` and add an entry to `ENTRIES`:

```php
[
    'gender' => 'female',          // 'female' | 'male'
    'energy' => 55,                // 0 (quiet) – 100 (bold); matches the personality slider
    'tags'   => ['street', 'urban', 'casual'], // use existing tag families below
    'niches' => ['lifestyle', 'fashion', 'any'], // 'any' matches every niche
    'text'   => 'Your full outfit description ending with "— no logos visible"',
],
```

Tag families (from the reference): `quiet, minimalist, clean, structured,
editorial, casual, urban, street, dark, moody, earthy, natural, bohemian,
cottagecore, y2k, playful, nostalgic, glam, bold, evening, sport, functional,
active, preppy, classic, polished, old-money, coastal`.

`selectWardrobe` first hard-filters by the vibe's primary tag, then scores
entries by energy distance (within ±28 of the personality) + tag overlap.
New tags are fine — they just won't be boosted by a vibe until you wire the
vibe in `VibeLibrary`.

### Add or adjust a vibe
In `VibeLibrary.php` add three keys for the new vibe name:

```php
PRIMARY_TAG['New Vibe'] = 'street';        // hard filter for selectWardrobe
TAG_MAP['New Vibe']     = ['street', 'urban'];  // soft tags
PALETTES['New Vibe']    = 'Palette: <one-line palette hint>.';
```

The human-facing vibe option (name, subtitle, allowed genders) lives in
`app/Support/PersonaOptions.php` (`VIBES`).

### Add a scene
In `SceneLibrary.php`:
- append a string to the pool for an existing niche in `POOLS`, or
- add a new niche → pool key in `NICHE_KEY` and a `POOLS` entry, or
- append to `OUTDOOR_CANDID` for generic outdoor backdrops.

A backstory archetype can `lockScene` a specific scene for its third variation.

### Add a time/lighting config
Append to `TimeLibrary::TIMES`:

```php
['label' => 'description of the light', 'lighting' => 'full lighting text'],
```

### Add a pose
In `PoseLibrary.php` add to `SOUL` or `MAIN`. Each value is
`['text' => '...{prop}...', 'alt' => '...no-prop text...']` where `{prop}` is
replaced with a prop name, and `alt` is used when the pose has no prop (or a
non-drink prop for the candid pose).

### Add a backstory archetype
Append to `BackstoryArchetypes::ENTRIES`:

```php
[
    'test' => '/your.?regex/',
    'tags' => ['sport', 'casual'],
    'sceneNiche' => 'fitness',        // must be a SceneLibrary pool key
    'buildHint' => 'lean, flexible',  // injected into the subject if build is blank
    'physicalDetail' => 'thin wire-frame glasses', // visible marker added to the subject
    'lockedScene' => 'yoga studio, ...',
],
```

Entries are matched top-down (`@preg_match` on the backstory); the first match
wins. `buildHint` / `physicalDetail` / `lockedScene` may be omitted (`null`).

## Notes

- Prompts contain intentional "anti-AI" markers and explicit camera/skin
  details — keep those when editing.
- Randomness uses `mt_rand` and the top-down match order, so results vary
  between generations while staying within the chosen vibe/niche.
- `physicalDesc()` produces the identity string reused by every later feature
  (character sheet, wardrobe, photo studio).