<?php

namespace App\Support\Prompt;

/**
 * Wardrobe library. Each entry carries a gender, an energy score (0 = quiet,
 * 100 = bold) that maps directly to the personality slider, tag families used
 * for vibe filtering, applicable niches, and the descriptive text.
 */
final class WardrobeLibrary
{
    public const ENTRIES = [
        // ── FEMALE ──────────────────────────────────────────────────
        // Quiet / understated (energy 0–20)
        [
            'gender' => 'female',
            'energy' => 5,
            'tags' => ['quiet', 'minimalist', 'natural'],
            'niches' => ['fashion', 'lifestyle', 'travel', 'any'],
            'text' => 'Oversized linen shirt worn as a dress, belted loosely at the waist with a thin leather cord, hem falling mid-thigh; bare legs; flat leather mule — no jewelry, no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 10,
            'tags' => ['quiet', 'minimalist', 'clean'],
            'niches' => ['fashion', 'beauty', 'lifestyle', 'any'],
            'text' => 'Wide-leg linen trousers, slightly cropped at the ankle; fine-knit long-sleeve top tucked in; no accessories; clean leather slide — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 12,
            'tags' => ['quiet', 'minimalist', 'structured'],
            'niches' => ['fashion', 'tech', 'finance', 'lifestyle'],
            'text' => 'Soft oversized turtleneck, hem just untucked; wide-leg tailored trousers, clean break; worn-in leather loafer — no accessories, no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 18,
            'tags' => ['quiet', 'glam', 'minimalist'],
            'niches' => ['fashion', 'lifestyle', 'any'],
            'text' => 'Slip dress in heavy satin, midi length, thin straps, no jewelry; flat leather thong sandal — no logos visible',
        ],

        // Understated with one intentional element (energy 20–40)
        [
            'gender' => 'female',
            'energy' => 22,
            'tags' => ['editorial', 'minimalist', 'structured'],
            'niches' => ['fashion', 'tech', 'finance'],
            'text' => 'Oversized blazer worn as a top, one button fastened, no shirt visible underneath; wide-leg trousers; clean pointed-toe leather flat — one thin gold chain, the only jewelry — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 28,
            'tags' => ['clean', 'minimalist', 'casual'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Straight-leg denim, clean hem; fitted ribbed tank top tucked in; two thin layered delicate gold chains at the collarbone; clean low-top leather sneakers — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 30,
            'tags' => ['quiet', 'minimalist', 'casual'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Worn oversized cashmere crewneck sweater, slightly pilling at the cuffs; slim straight denim; clean leather loafer; small pebbled leather crossbody — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 35,
            'tags' => ['minimalist', 'clean', 'travel'],
            'niches' => ['travel', 'lifestyle', 'fashion'],
            'text' => 'Linen shirt, collar open, sleeves slightly rolled once at the forearm; high-waist straight-leg linen trousers, clean hem; leather strappy sandal, minimal ankle strap — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 38,
            'tags' => ['preppy', 'classic', 'polished'],
            'niches' => ['fashion', 'lifestyle', 'tech'],
            'text' => 'Fitted polo shirt, collar up slightly; high-waist straight-leg chino; leather sneaker, clean; a single thin gold band ring — no logos visible',
        ],

        // Balanced / versatile (energy 40–60)
        [
            'gender' => 'female',
            'energy' => 42,
            'tags' => ['casual', 'clean', 'urban'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Oversized vintage-wash tee, neckline slightly relaxed, tucked loosely at the front; high-waist straight-leg denim; low-top canvas shoe, faintly scuffed — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 45,
            'tags' => ['minimalist', 'structured', 'clean'],
            'niches' => ['fashion', 'lifestyle', 'tech'],
            'text' => 'Fitted ribbed mock-neck top; tailored wide-leg trousers, clean hem; minimal leather loafer; a thin gold bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 48,
            'tags' => ['earthy', 'casual', 'natural'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Well-worn straight-leg medium-wash denim; linen shirt tucked in, collar open; small simple gold hoop earrings; clean sneaker — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 52,
            'tags' => ['casual', 'quiet', 'cottagecore'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Long open cardigan in soft knit over a simple fitted tee; straight-leg jeans; leather low sneaker; a thin woven cord bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 55,
            'tags' => ['bohemian', 'earthy', 'natural'],
            'niches' => ['travel', 'lifestyle', 'wellness'],
            'text' => 'Flowy midi dress in lightweight gauze or crinkled cotton, slightly tiered skirt, adjustable thin straps; flat leather sandal worn soft at the footbed; layered cord and thin beaded bracelets on both wrists, a simple thin pendant necklace — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 58,
            'tags' => ['clean', 'casual', 'sport'],
            'niches' => ['fitness', 'lifestyle', 'any'],
            'text' => 'Oversized cotton hoodie, slightly cropped, hood down; bike shorts; thick-sole trainer; small silver hoop earrings — no logos visible',
        ],

        // Personality pieces emerging (energy 60–80)
        [
            'gender' => 'female',
            'energy' => 62,
            'tags' => ['editorial', 'urban', 'street'],
            'niches' => ['fashion', 'entertainment', 'lifestyle'],
            'text' => 'Fluid-cut collarless oversized blazer worn open over a fitted ribbed bodysuit or tank; clean straight-leg tailored trousers in a contrasting neutral; pointed-toe leather mule, slight block heel; one sculptural resin or stone ring — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 65,
            'tags' => ['y2k', 'casual', 'playful'],
            'niches' => ['lifestyle', 'entertainment', 'fashion'],
            'text' => 'Y2K-adjacent low-rise straight-leg medium-wash denim; fitted ribbed halter top; a thin silver chain belt; clean low-top sneaker — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 68,
            'tags' => ['earthy', 'bohemian', 'natural'],
            'niches' => ['travel', 'lifestyle', 'fashion'],
            'text' => 'Wrap blouse in lightweight viscose, tied at the front, slightly billowing at the sleeves; high-waist wide-leg denim; leather wedge espadrille; two thin layered necklaces — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 72,
            'tags' => ['dark', 'moody', 'street'],
            'niches' => ['entertainment', 'fashion', 'lifestyle'],
            'text' => 'Oversized faded graphic tee, neckline slightly wide, tucked at the front into high-waist straight-leg denim with subtle knee distress; chunky platform boot, zip detail at ankle; a worn leather cuff — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 75,
            'tags' => ['casual', 'street', 'y2k'],
            'niches' => ['lifestyle', 'entertainment', 'fashion'],
            'text' => 'Cropped raw-edge denim jacket worn open over a fitted ribbed tank; high-waist flared denim; a simple thin leather belt; clean vintage leather sneaker — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 78,
            'tags' => ['editorial', 'structured', 'polished'],
            'niches' => ['fashion', 'finance', 'tech'],
            'text' => 'Sharply tailored double-breasted blazer with a strong shoulder, worn as the only top layer; matching wide-leg trouser; clean pointed-toe leather oxford; a single architectural earring — no logos visible',
        ],

        // Bold / expressive (energy 80–100)
        [
            'gender' => 'female',
            'energy' => 82,
            'tags' => ['glam', 'editorial', 'bold'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Faux-fur trim coat, worn open; fitted ribbed turtleneck underneath; straight tailored trousers; pointed-toe ankle boot; statement oversized resin drop earrings — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 88,
            'tags' => ['editorial', 'bold', 'structured'],
            'niches' => ['fashion'],
            'text' => 'Wide-leg trousers in structured crepe; matching blazer worn open over a barely-there bralette; sculptural square-toe block heel; one architectural cuff — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 92,
            'tags' => ['editorial', 'dark', 'glam'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Sheer organza blouse, buttons to the collar, translucent over a bralette; wide-leg tailored trousers; strappy heeled sandal; a single large sculptural resin ring — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 96,
            'tags' => ['bold', 'playful', 'editorial'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Cropped structured blazer worn open, no top underneath; wide-leg tailored trouser; pointed-toe leather kitten heel; a stack of thin gold rings — no logos visible',
        ],

        // Dressy / glam at medium energy
        [
            'gender' => 'female',
            'energy' => 48,
            'tags' => ['glam', 'evening', 'polished'],
            'niches' => ['fashion', 'lifestyle', 'entertainment', 'any'],
            'text' => 'Satin slip midi dress, thin adjustable straps, fabric draping softly at the hip with a subtle liquid sheen; dainty thin-strap sandal; two delicate layered chains at the collarbone — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 56,
            'tags' => ['glam', 'editorial', 'structured'],
            'niches' => ['fashion', 'entertainment', 'lifestyle'],
            'text' => 'Tailored blazer dress, single-button, hitting mid-thigh with slight shoulder structure; sheer tights; pointed-toe kitten heel; a simple gold bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 62,
            'tags' => ['glam', 'evening', 'polished'],
            'niches' => ['fashion', 'entertainment', 'any'],
            'text' => 'Fitted ribbed knit midi dress with a modest scoop neckline; strappy heeled sandal; thin layered gold chains and small sculptural gold hoops — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 70,
            'tags' => ['glam', 'bold', 'evening'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Silky wrap midi dress, thin tie at the waist, the fabric catching light as she moves; pointed-toe strappy heeled sandal; gold drop earrings and a single thin chain — no logos visible',
        ],

        // Dark & Moody (expanded) — all entries embed explicit dark colors so Soul model renders correctly
        [
            'gender' => 'female',
            'energy' => 60,
            'tags' => ['dark', 'moody', 'structured'],
            'niches' => ['fashion', 'entertainment', 'lifestyle'],
            'text' => 'Fitted ribbed all-black turtleneck, long sleeve; high-waist wide-leg tailored trousers in black or deep charcoal; pointed-toe ankle boot in black leather; a single thin silver chain at the collarbone — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 65,
            'tags' => ['dark', 'moody', 'editorial'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Midi-length slip-style dress in deep black matte satin, thin straps, fabric skimming the body closely; barely-there heeled sandal in black; a thin silver chain stacked with a small drop pendant — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 78,
            'tags' => ['dark', 'moody', 'street'],
            'niches' => ['fashion', 'entertainment', 'lifestyle'],
            'text' => 'Oversized structured black leather-look jacket, slightly stiff at the shoulder; fitted ribbed black midi skirt underneath; chunky black lug-sole boot; no jewelry — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 85,
            'tags' => ['dark', 'moody', 'editorial'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Long draped overcoat in black or deep charcoal, minimal lapel, worn open or loosely belted; slim-cut trousers in the same dark tone; sleek pointed-toe boot in black leather; one architectural sculptural ring — no logos visible',
        ],

        // Cottagecore (expanded)
        [
            'gender' => 'female',
            'energy' => 30,
            'tags' => ['cottagecore', 'natural', 'earthy'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Flowy midi-length printed cotton dress, fitted at the chest with a square neckline and soft gathered skirt; flat leather sandal with a simple strap; small delicate stud earrings — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 42,
            'tags' => ['cottagecore', 'earthy', 'quiet'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Linen pinafore dress, adjustable shoulder straps, worn over a long-sleeve fitted top; simple leather mule; small woven basket bag — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 55,
            'tags' => ['cottagecore', 'natural', 'bohemian'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Puff-sleeve cotton blouse with smocking at the wrists, collar slightly open; high-waist straight-leg denim; leather ankle boot, round-toe; a small delicate crossbody bag — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 65,
            'tags' => ['cottagecore', 'earthy', 'editorial'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Prairie-style cotton midi dress with a fitted bodice, long flowing skirt, and small puffed sleeves; leather loafer or low boot; thin layered necklaces, dainty and botanical in feel — no logos visible',
        ],

        // Coastal (female)
        [
            'gender' => 'female',
            'energy' => 25,
            'tags' => ['coastal', 'natural', 'quiet'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Wide-leg linen trousers, slightly relaxed; simple fitted ribbed tank, thin straps; flat leather sandal, worn soft; no jewelry — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 42,
            'tags' => ['coastal', 'natural', 'casual'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Linen button-down shirt, oversized and half-tucked, sleeves rolled high; straight-leg denim cut-offs, frayed hem at mid-thigh; flat leather slide; thin woven cord anklet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 58,
            'tags' => ['coastal', 'natural', 'earthy'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Lightweight crochet cover-up worn over a simple ribbed bralette, hem hitting mid-thigh; flat woven sandal; layered cord and natural-material necklaces — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 72,
            'tags' => ['coastal', 'natural', 'bohemian'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Flowy wrap midi skirt in lightweight woven fabric, tied at the hip; fitted linen crop top; strappy flat sandal; layered shell and cord jewellery, sun-worn and natural — no logos visible',
        ],

        // Fitness/sport — gym context
        [
            'gender' => 'female',
            'energy' => 30,
            'tags' => ['sport', 'functional', 'clean'],
            'niches' => ['fitness', 'wellness', 'lifestyle'],
            'text' => 'Seamless sports bra with subtle vertical ribbing; matching high-waist compression leggings, slight sheen at the hip; clean training shoe — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 40,
            'tags' => ['sport', 'earthy', 'natural'],
            'niches' => ['fitness', 'wellness', 'lifestyle'],
            'text' => 'Seamless sports bra with subtle cross-back detail; matching high-waist compression leggings; trail-running shoe — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 50,
            'tags' => ['sport', 'clean', 'natural'],
            'niches' => ['fitness', 'wellness', 'lifestyle'],
            'text' => 'Matching set: ribbed high-neck sports bra and high-waist leggings; clean low-profile trainer; small pearl stud earrings — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 65,
            'tags' => ['sport', 'earthy', 'casual'],
            'niches' => ['fitness', 'wellness', 'lifestyle'],
            'text' => 'Sports bra with wide straps; matching full-length high-waist leggings; clean gum-sole running shoe — a thin gold chain necklace — no logos visible',
        ],

        // Athleisure — sporty vibe for non-gym lifestyle contexts
        [
            'gender' => 'female',
            'energy' => 48,
            'tags' => ['sport', 'casual', 'clean'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Wide-leg track pants in soft jersey fabric, slight sheen; fitted ribbed crop top with thin straps; clean low-profile trainer; small gold hoops — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 55,
            'tags' => ['sport', 'casual', 'urban'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Pleated tennis mini skirt; fitted ribbed athletic tank, thin straps; clean low-top leather sneaker; a thin chain necklace — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 62,
            'tags' => ['sport', 'casual', 'street'],
            'niches' => ['lifestyle', 'fashion', 'entertainment', 'any'],
            'text' => 'Fitted zip-up track jacket, zipped halfway; matching wide-leg track pants; chunky clean trainer; small hoop earrings — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 75,
            'tags' => ['sport', 'casual', 'playful'],
            'niches' => ['lifestyle', 'fashion', 'entertainment', 'any'],
            'text' => 'Pleated tennis mini skirt; fitted cropped athletic zip-up, zipped halfway; clean chunky-sole trainer; small gold hoops — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 85,
            'tags' => ['sport', 'casual', 'bold'],
            'niches' => ['lifestyle', 'fashion', 'entertainment'],
            'text' => 'Pleated micro tennis mini skirt; fitted cropped graphic athletic tee, slightly oversized at the shoulder; bold chunky trainer with a thick sole; stacked thin gold chains — no logos visible',
        ],

        // ── MALE ────────────────────────────────────────────────────
        // Quiet / understated (energy 0–20)
        [
            'gender' => 'male',
            'energy' => 8,
            'tags' => ['quiet', 'minimalist', 'natural'],
            'niches' => ['fashion', 'lifestyle', 'travel', 'any'],
            'text' => 'Washed linen overshirt, all buttons done, sleeves folded; loose straight-leg trousers; leather sandal; no accessories — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 15,
            'tags' => ['quiet', 'minimalist', 'clean'],
            'niches' => ['fashion', 'tech', 'finance', 'lifestyle'],
            'text' => 'Soft cotton crewneck sweater, slightly oversize; straight-leg wool-blend trousers, clean break at the ankle; clean leather low sneaker — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 18,
            'tags' => ['quiet', 'structured', 'minimalist'],
            'niches' => ['tech', 'finance', 'fashion'],
            'text' => 'Fine-knit turtleneck; tailored straight-leg dark trousers; clean leather oxford; no accessories — no logos visible',
        ],

        // Understated with one intentional element (energy 20–40)
        [
            'gender' => 'male',
            'energy' => 25,
            'tags' => ['minimalist', 'earthy', 'casual'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Unstructured linen jacket worn open; fitted tee underneath; slim straight denim; leather loafer — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 30,
            'tags' => ['minimalist', 'quiet', 'preppy'],
            'niches' => ['tech', 'finance', 'lifestyle'],
            'text' => 'Soft quarter-zip merino sweater; slim straight chino; clean low-profile sneaker; a simple stainless steel watch — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 35,
            'tags' => ['casual', 'clean', 'natural'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Relaxed-fit linen button-down, collar open, tucked at front only; straight-leg medium-wash denim; clean suede chukka — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 38,
            'tags' => ['preppy', 'classic', 'clean'],
            'niches' => ['lifestyle', 'fashion', 'tech'],
            'text' => 'Relaxed Oxford shirt, collar open one button; slim straight-leg chino; clean leather low sneaker — a single thin woven bracelet — no logos visible',
        ],

        // Balanced (energy 40–60)
        [
            'gender' => 'male',
            'energy' => 42,
            'tags' => ['casual', 'clean', 'urban'],
            'niches' => ['any'],
            'text' => 'Heavyweight cotton tee, crew neck slightly relaxed from washing; well-worn straight-leg dark denim; clean low-profile trainer — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 45,
            'tags' => ['casual', 'dark', 'urban'],
            'niches' => ['lifestyle', 'fashion', 'entertainment'],
            'text' => 'Washed overshirt worn open over a fitted tee; slim straight dark jeans; clean leather low sneaker — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 50,
            'tags' => ['earthy', 'casual', 'natural'],
            'niches' => ['lifestyle', 'travel'],
            'text' => 'Soft relaxed-fit shirt, collar open two buttons, sleeves rolled; straight-leg chino; leather loafer — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 55,
            'tags' => ['minimalist', 'casual', 'clean'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Relaxed-fit linen shirt, collar open, slightly untucked; straight-leg denim; clean canvas slip-on; no accessories — no logos visible',
        ],

        // Dark & Moody (male) — all entries embed explicit dark colors so Soul model renders correctly
        [
            'gender' => 'male',
            'energy' => 55,
            'tags' => ['dark', 'moody', 'casual'],
            'niches' => ['lifestyle', 'entertainment', 'fashion'],
            'text' => 'Oversized ribbed crewneck sweater in black or deep charcoal, relaxed fit, hem sitting at the hip; dark straight-leg denim; clean black leather low sneaker; a worn thin leather cord bracelet — no logos visible',
        ],

        // Personality pieces (energy 60–80)
        [
            'gender' => 'male',
            'energy' => 62,
            'tags' => ['street', 'urban', 'casual'],
            'niches' => ['fashion', 'entertainment', 'lifestyle'],
            'text' => 'Washed oversized heavyweight jersey crewneck, dropped shoulder seam; straight-leg dark denim, clean hem; clean low-profile leather sneakers, toe box faintly creased — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 68,
            'tags' => ['urban', 'casual', 'dark'],
            'niches' => ['lifestyle', 'travel', 'entertainment'],
            'text' => 'Waxed canvas overshirt, collar slightly popped; fitted tee underneath; slim straight dark denim; clean leather low sneaker; a worn thin leather cord necklace — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 72,
            'tags' => ['street', 'y2k', 'urban'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Wide-leg carpenter denim with subtle hardware at the thigh pocket; oversized washed tee, hem asymmetric; clean chunky trainer; a thin chain around the neck — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 75,
            'tags' => ['street', 'casual', 'urban'],
            'niches' => ['lifestyle', 'entertainment'],
            'text' => 'Oversized washed crewneck hoodie, drawstrings slightly uneven; wide-leg sweatpant, tapered at the ankle; clean low-top canvas shoe; a small hoop earring — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 78,
            'tags' => ['editorial', 'structured', 'dark'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Long black overcoat in a heavy wool-blend, collar turned up; fitted black turtleneck; slim straight trouser in the same dark tone; clean leather chelsea boot in black; no accessories — no logos visible',
        ],

        // Bold / expressive (energy 80–100)
        [
            'gender' => 'male',
            'energy' => 82,
            'tags' => ['dark', 'moody', 'editorial'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Heavy structured overcoat in all-black, draped open; fitted black turtleneck underneath; slim straight trouser in black; pointed-toe leather boot in black; one thin silver chain at the collarbone — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 85,
            'tags' => ['editorial', 'bold', 'glam'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Wide-leg patterned suit trouser; matching blazer worn open, no shirt, a thin chain at the collarbone; clean pointed-toe leather oxford — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 92,
            'tags' => ['editorial', 'bold', 'colorful'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Wide-leg cord trousers; fitted ribbed turtleneck; clean leather boot; a stack of thin silver rings — no logos visible',
        ],

        // Male fitness — gym context
        [
            'gender' => 'male',
            'energy' => 30,
            'tags' => ['sport', 'functional', 'clean'],
            'niches' => ['fitness', 'lifestyle'],
            'text' => 'Fitted performance tee, slightly damp at the collar; tapered training shorts with small side-slit; clean training shoe, toe box faintly scuffed — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 50,
            'tags' => ['sport', 'casual', 'street'],
            'niches' => ['fitness', 'lifestyle'],
            'text' => 'Oversized washed hoodie, hem hitting mid-thigh; fitted training short; clean minimalist training shoe — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 40,
            'tags' => ['sport', 'clean', 'functional'],
            'niches' => ['fitness', 'lifestyle'],
            'text' => 'Tech-fabric sleeveless training vest, slight drape; compression training shorts; clean trainer — no logos visible',
        ],

        // Male athleisure — sporty vibe for lifestyle contexts
        [
            'gender' => 'male',
            'energy' => 45,
            'tags' => ['sport', 'casual', 'urban'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Straight-leg jogger in soft jersey, slightly tapered at the ankle; clean fitted crewneck; low-profile trainer; no accessories — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 60,
            'tags' => ['sport', 'casual', 'street'],
            'niches' => ['lifestyle', 'entertainment', 'any'],
            'text' => 'Athletic shorts, mid-thigh, slight technical drape; clean fitted tee; clean mid-top trainer; a thin cord bracelet — no logos visible',
        ],

        // Coastal (male)
        [
            'gender' => 'male',
            'energy' => 20,
            'tags' => ['coastal', 'natural', 'quiet'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Loose straight-leg linen trousers, slightly creased from wear; simple fitted cotton tee; leather sandal; no accessories — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 35,
            'tags' => ['coastal', 'casual', 'natural'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Linen short-sleeve shirt, collar open three buttons, slightly oversized, half-tucked; straight-leg chino shorts, hem at mid-thigh; leather sandal; a thin cord necklace — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 52,
            'tags' => ['coastal', 'casual', 'earthy'],
            'niches' => ['lifestyle', 'travel'],
            'text' => 'Worn canvas shorts, slightly baggy, faded at the hem; fitted cotton tee, slightly faded; flat leather sandal; a thin leather wristband — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 65,
            'tags' => ['coastal', 'natural', 'casual'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Loose linen trousers, cropped slightly above the ankle; linen overshirt, collar open, sleeves rolled; woven sandal; a small pendant on a cord — no logos visible',
        ],

        // ── OLD MONEY / QUIET LUXURY ─────────────────────────────────
        [
            'gender' => 'male',
            'energy' => 15,
            'tags' => ['old-money', 'classic', 'quiet', 'polished'],
            'niches' => ['lifestyle', 'fashion', 'travel', 'any'],
            'text' => 'Unstructured linen blazer, lapels slightly soft from wear, over an Oxford shirt with three buttons casually open; slim straight-leg chino with a faint crease; penny loafer in full-grain leather, slightly worn at the heel; a chunky gold signet ring on the left pinky; a simple leather-strap watch on the wrist — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 18,
            'tags' => ['old-money', 'natural', 'casual'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Fine-knit cotton polo, very relaxed fit, lightly sun-faded; well-worn straight-leg denim with a clean hem; leather tennis sneaker, slightly soft at the toe; a gold signet ring on the left pinky; a steel watch on a leather strap — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 22,
            'tags' => ['old-money', 'preppy', 'polished'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Linen blazer worn open over a faded linen shirt, collar three buttons open, sleeves pushed; straight-leg chino; leather loafer, worn smooth at the sole edge; a thin gold watch with a worn leather strap — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 28,
            'tags' => ['old-money', 'casual', 'quiet'],
            'niches' => ['lifestyle', 'travel'],
            'text' => 'Soft washed Oxford shirt, oversized and fully untucked, buttons open to mid-chest; well-worn straight-leg chino; faded canvas boat shoe; a gold signet ring; a simple leather-strap watch — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 20,
            'tags' => ['old-money', 'structured', 'quiet'],
            'niches' => ['fashion', 'lifestyle'],
            'text' => 'Tailored linen trousers with a clean crease; a linen shirt, collar open, untucked; leather loafer with a light scuff at the toe; a thin gold chain at the collarbone; a slim leather-strap watch — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 35,
            'tags' => ['old-money', 'classic', 'relaxed'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Soft cable-knit crewneck sweater, slightly oversize, hem relaxed over the waistband; slim straight-leg chino; clean leather low sneaker; a gold signet ring; no other jewelry — no logos visible',
        ],

        [
            'gender' => 'female',
            'energy' => 12,
            'tags' => ['old-money', 'classic', 'quiet', 'polished'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Perfectly tailored wide-leg linen trousers; a fine-knit short-sleeve polo, tucked neatly; a single strand of small pearls at the collarbone; leather loafer, buffed; a simple gold bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 18,
            'tags' => ['old-money', 'natural', 'casual'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Oversize washed Oxford shirt, three buttons open, half-tucked into straight-leg linen trousers; gold signet ring; worn-in leather sandal with a minimal strap — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 22,
            'tags' => ['old-money', 'preppy', 'polished'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Slim-fit blazer, single button, worn over a striped poplin shirt, collar open; straight-leg chino, clean hem; flat leather loafer; a thin gold chain and small pearl stud earrings — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 28,
            'tags' => ['old-money', 'classic', 'quiet'],
            'niches' => ['lifestyle', 'any'],
            'text' => 'Soft cashmere crewneck sweater, slightly oversize; straight-leg linen trousers; clean leather sneaker; a thin gold chain; a small leather tote held at the crook of the arm — no logos visible',
        ],

        // ── CLEAN GIRL — dedicated entries (distinct from generic Minimalist) ─
        [
            'gender' => 'female',
            'energy' => 22,
            'tags' => ['clean', 'natural', 'quiet'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Fitted ribbed tank top tucked neatly into a high-waist tailored midi skirt in warm ecru or oat; flat leather sandal, thin strap; small gold huggie hoop earrings; no other jewelry — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 38,
            'tags' => ['clean', 'natural', 'casual'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Matching ribbed set: fitted long-sleeve crop top and wide-leg ribbed lounge trousers in warm oat or cream; clean leather low sneaker or flat; small gold huggie hoops; a single thin delicate gold chain — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 55,
            'tags' => ['clean', 'natural', 'casual'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Fitted white or cream ribbed tank tucked into wide-leg linen or satin-finish trousers in camel or warm oat; strappy leather flat sandal; small gold hoop earrings; a layered delicate gold chain necklace — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 68,
            'tags' => ['clean', 'natural', 'polished'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Fitted satin-finish or ribbed cami midi dress in warm ecru or nude; strappy leather flat sandal; gold hoop earrings; stacked thin delicate gold chains and a simple gold bracelet — no logos visible',
        ],

        // ── STREETWEAR — low & mid energy (was missing entirely below 62) ─
        [
            'gender' => 'female',
            'energy' => 22,
            'tags' => ['street', 'casual', 'clean'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Oversized washed heavyweight tee, crew neck, hem slightly cropped by tucking once at the front; high-waist straight-leg dark denim; clean white low-top canvas sneaker, slightly broken in — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 38,
            'tags' => ['street', 'casual', 'urban'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Relaxed straight-leg dark denim; fitted ribbed tank, thin straps, half-tucked; clean low-profile leather sneaker; a small thin chain at the collarbone — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 52,
            'tags' => ['street', 'urban', 'casual'],
            'niches' => ['lifestyle', 'fashion', 'entertainment', 'any'],
            'text' => 'Wide-leg dark denim, clean hem; fitted graphic tee tucked loosely at one side; clean chunky low-top trainer; a thin chain layered over the tee — no logos visible',
        ],

        [
            'gender' => 'male',
            'energy' => 22,
            'tags' => ['street', 'casual', 'clean'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Straight-leg dark denim, clean hem; heavyweight cotton tee, crew neck, slightly oversized; clean white low-top canvas sneaker — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 38,
            'tags' => ['street', 'urban', 'casual'],
            'niches' => ['lifestyle', 'fashion', 'entertainment'],
            'text' => 'Straight-leg dark denim; loose overshirt worn fully open over a fitted tee; clean low-profile leather sneaker — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 52,
            'tags' => ['street', 'casual', 'urban'],
            'niches' => ['lifestyle', 'fashion', 'entertainment'],
            'text' => 'Wide-leg dark denim, clean hem; oversized washed crewneck, dropped shoulder; clean low-profile trainer; a thin cord bracelet — no logos visible',
        ],

        // ── BOHEMIAN — male (was zero entries) ───────────────────────────
        [
            'gender' => 'male',
            'energy' => 28,
            'tags' => ['bohemian', 'earthy', 'natural'],
            'niches' => ['lifestyle', 'travel', 'any'],
            'text' => 'Loose linen shirt, collar open three buttons, slightly oversized, untucked; relaxed straight-leg linen trousers; flat leather sandal; a single thin cord or wooden-bead bracelet — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 48,
            'tags' => ['bohemian', 'natural', 'earthy'],
            'niches' => ['lifestyle', 'travel'],
            'text' => 'Loose woven cotton shirt, slightly sun-faded, collar open; relaxed wide-leg linen trousers, cropped above the ankle; flat leather sandal; a layered cord and natural-bead necklace — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 65,
            'tags' => ['bohemian', 'earthy', 'casual'],
            'niches' => ['lifestyle', 'travel', 'fashion'],
            'text' => 'Linen overshirt worn open, fabric slightly crumpled; wide-leg cotton drawstring trousers; leather sandal with a woven strap; layered thin cord necklaces, worn and natural — no logos visible',
        ],

        // ── GLAM — male low & mid energy (was only 1 entry at energy 85) ─
        [
            'gender' => 'male',
            'energy' => 32,
            'tags' => ['glam', 'polished', 'structured'],
            'niches' => ['fashion', 'lifestyle', 'entertainment'],
            'text' => 'Tailored straight-leg trousers in a subtle satin-finish fabric; fitted fine-knit turtleneck; clean leather chelsea boot; a single thin gold chain — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 55,
            'tags' => ['glam', 'evening', 'polished'],
            'niches' => ['fashion', 'entertainment', 'lifestyle'],
            'text' => 'Fitted ribbed turtleneck; slim tailored trousers with a faint sheen at the fabric; clean pointed-toe leather oxford; a thin gold bracelet — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 68,
            'tags' => ['glam', 'bold', 'evening'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Satin-finish button-up shirt, collar open two buttons, slightly relaxed; well-tailored slim trousers; clean pointed-toe leather boot; a gold signet ring and thin chain at the collarbone — no logos visible',
        ],

        // ── Y2K — low & mid energy (was all 62+ for female, 72 for male) ─
        [
            'gender' => 'female',
            'energy' => 28,
            'tags' => ['y2k', 'playful', 'casual'],
            'niches' => ['lifestyle', 'fashion', 'entertainment'],
            'text' => 'Low-rise straight-leg denim, slightly flared at the hem; fitted ribbed baby tee, slightly cropped, scoop neck; flat strappy sandal; a thin chain bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 48,
            'tags' => ['y2k', 'casual', 'clean'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Low-rise bootcut denim in a clean medium wash; cropped fitted polo, collar slightly open; clean white low-top sneaker; a delicate thin choker-style necklace — no logos visible',
        ],

        [
            'gender' => 'male',
            'energy' => 32,
            'tags' => ['y2k', 'casual', 'urban'],
            'niches' => ['lifestyle', 'fashion', 'entertainment'],
            'text' => 'Wide-leg straight denim, clean medium wash; fitted tee with a subtle graphic, crew neck; clean low-top sneaker; a thin chain necklace — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 52,
            'tags' => ['y2k', 'urban', 'casual'],
            'niches' => ['lifestyle', 'entertainment'],
            'text' => 'Baggy low-rise straight-leg denim, slightly faded at the knees; zip-up track jacket in a tonal colour, collar slightly popped, worn open over a fitted tee; clean low-top skate-style sneaker; a thin silver chain at the collarbone — no logos visible',
        ],

        // ── EDITORIAL — male mid-range (was only 78, 82, 85) ─────────────
        [
            'gender' => 'male',
            'energy' => 42,
            'tags' => ['editorial', 'structured', 'clean'],
            'niches' => ['fashion', 'lifestyle'],
            'text' => 'Collarless structured cotton jacket, slightly boxy, worn fully buttoned as the only top layer — no shirt visible; clean straight-leg tailored trousers in a contrasting neutral; square-toe leather oxford; no accessories — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 60,
            'tags' => ['editorial', 'structured', 'dark'],
            'niches' => ['fashion', 'entertainment'],
            'text' => 'Structured overshirt in heavy black fabric worn fully closed as a jacket, strong shoulder line; fitted black turtleneck underneath; slim tailored trousers in dark charcoal; clean black leather chelsea boot — no logos visible',
        ],

        // ── DARK & MOODY — female low energy (was nothing below 60) ──────
        [
            'gender' => 'female',
            'energy' => 22,
            'tags' => ['dark', 'minimalist', 'quiet'],
            'niches' => ['fashion', 'lifestyle', 'entertainment'],
            'text' => 'Fine-knit fitted black turtleneck, long sleeve, slightly thin at the cuffs; slim straight-leg black trousers, clean hem; clean black leather flat; no jewelry — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 38,
            'tags' => ['dark', 'moody', 'casual'],
            'niches' => ['fashion', 'lifestyle', 'entertainment'],
            'text' => 'Fitted ribbed long-sleeve crew-neck top in black or deep charcoal; slim straight dark denim or black trousers; clean leather ankle boot in black, low block heel; a single thin silver chain necklace — no logos visible',
        ],

        // ── PREPPY — expanded (was 2F / 2M) ──────────────────────────────
        [
            'gender' => 'female',
            'energy' => 25,
            'tags' => ['preppy', 'classic', 'clean'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Fine-knit crewneck sweater in a clean neutral, slightly fitted; slim straight-leg chino; leather loafer; a simple thin gold bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 55,
            'tags' => ['preppy', 'classic', 'polished'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Striped poplin shirt, collar open, tucked into high-waist straight-leg chino; leather loafer; small pearl stud earrings; a simple gold bracelet — no logos visible',
        ],
        [
            'gender' => 'female',
            'energy' => 68,
            'tags' => ['preppy', 'editorial', 'polished'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Slim blazer in a classic check, single button, lapels neat; fitted crew-neck tee underneath; straight-leg chino; penny loafer; a thin gold chain — no logos visible',
        ],

        [
            'gender' => 'male',
            'energy' => 22,
            'tags' => ['preppy', 'classic', 'quiet'],
            'niches' => ['lifestyle', 'fashion', 'any'],
            'text' => 'Soft cotton crewneck sweater, slightly relaxed, in a clean neutral; well-worn slim straight-leg chino; leather loafer, buffed slightly; a thin stainless watch — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 55,
            'tags' => ['preppy', 'classic', 'clean'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Oxford button-down, collar open, slightly tucked at the front; straight-leg chino, clean break; leather loafer; a simple woven cord bracelet — no logos visible',
        ],
        [
            'gender' => 'male',
            'energy' => 68,
            'tags' => ['preppy', 'polished', 'editorial'],
            'niches' => ['lifestyle', 'fashion'],
            'text' => 'Linen blazer with a natural rumple, worn open; fitted Oxford shirt underneath, collar two buttons open; slim chino; leather loafer; a gold signet ring — no logos visible',
        ],
    ];
}
