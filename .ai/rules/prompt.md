---
paths:
  - 'app/Support/Prompt/**'
---

# Prompt

## Prompt library classes are faithful ports of the React reference app
app/Support/Prompt/* are final static-library classes (no Laravel deps) ported verbatim from REFERENCE/ai-persona/src/utils/systemPrompt.js (and buildPhysicalDescString from pages/Create.jsx). Do not truncate WARDROBE (119 entries) or BACKSTORY_ARCHETYPES (100 entries); preserve regex strings and em-dash prose exactly. Randomness uses mt_rand where the JS used Math.random. Update the JS source first, then re-port, if prompt behavior changes.

## 3 samples = 3 different personas (facial identities)
The 3 variation prompts must render DIFFERENT people, not 3 angles of the same person. `buildThreeVariationPrompts` assigns each sample a distinct facial identity via `facialIdentities()` (face shape × brows × feature marker, rotated randomly per call so sets differ), injected as `facialDetail` and rendered into the Subject line as ", with …". All user-selected attributes (hair/eyes/skin/build/ethnicity) stay fixed. If a `faceRef` is set, facial variation is skipped (same face). When editing prompts, keep per-sample facial distinctness or the samples look identical.
