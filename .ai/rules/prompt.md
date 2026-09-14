---
paths:
  - 'app/Support/Prompt/**'
---

# Prompt

## Prompt library classes are faithful ports of the React reference app
app/Support/Prompt/* are final static-library classes (no Laravel deps) ported verbatim from REFERENCE/ai-influencer/src/utils/systemPrompt.js (and buildPhysicalDescString from pages/Create.jsx). Do not truncate WARDROBE (119 entries) or BACKSTORY_ARCHETYPES (100 entries); preserve regex strings and em-dash prose exactly. Randomness uses mt_rand where the JS used Math.random. Update the JS source first, then re-port, if prompt behavior changes.
