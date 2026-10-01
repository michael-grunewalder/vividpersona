---
paths:
  - 'app/Services/**'
---

# Services

## Persona generation: per-team service credentials + image models from ai_models
Per-team credentials live in `team_api_credentials` (encrypted `credentials` keyed by `service`; the `ApiService` enum in app/Enums carries label/type/fields/models/lab/config-key from config/services.php; resolve via `Team::credentialsFor()`/`hasCredential()`). `MediaService::generateImages(AiModel $model, ...)` routes `fal` → FalClient queue API, `wavespeed` → WaveSpeed v3 submit/poll; the provider, endpoint and request options come from the selected `ai_models` row (see `.ai/rules/media.md`). Media providers are only offered when the team has a credential; the wizard lists models for the team's `defaultMediaService()`. `GenerateImageJob` builds 3 variation prompts from `PersonaPromptBuilder`, optionally analyzing the backstory (`BackstoryAnalyst`) to inject a `backstoryContext` and enhancing (`PromptEnhancer`). LLM choice = wizard override (`persona.llm_provider`) → team default (`teams.default_llm`) → first connected LLM, resolved by `LlmClientFactory::forTeam()`; each client (`ClaudeClient`/`ChatGptClient`/`DeepSeekClient`) injects the team key via `config("ai.providers.<config_key>.key")`. Extend the prompt library per docs/persona-prompting.md.