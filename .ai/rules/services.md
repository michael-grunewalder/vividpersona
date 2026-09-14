---
paths:
  - 'app/Services/**'
---

# Services

## Influencer generation: per-team providers + prompt library
Influencer avatars: per-team credentials live in `provider_connections` (encrypted `credentials` keyed by the ApiProvider `meta` field names; resolve via `Team::providerCredentials()`). `MediaService` routes by `ApiProvider.machine_name` ('fal' → FalClient queue API, 'wavespeed' → WaveSpeed v3 submit/poll). `GenerateInfluencerSetJob` builds 3 variation prompts from `InfluencerPromptBuilder` (app/Support/Prompt) and appends a `generation_history` set; the user picks one to set `main_image`. Claude (`BackstoryAnalyst`/`PromptEnhancer`) reads the team's LLM key by setting `config('ai.providers.anthropic.key')` at call time. Extend the prompt library per docs/influencer-prompting.md.
