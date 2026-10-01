---
paths:
  - 'app/Services/Media/**'
  - app/Services/Media/PersonaMediaService.php
---

# Media

## Image model catalog is DB-driven: ai_models, per concrete endpoint
Image models come from the `ai_models` table (admin-curated under `/backend/ai-model`), one row per concrete provider endpoint. `AiModelCatalog` (app/Services/Media) exposes enabled rows as a `family → version → variant` selection tree for a given type + provider, and `find()` resolves an enabled model by id. Payloads are built by `MediaService` from each model's `options` JSON (`size` maps 9:16/16:9 into the model's own param; `defaults` are extra request fields) — never hardcode a size param per provider. There is no live WaveSpeed fetch anymore; WaveSpeed endpoint ids are captured in `ai_models` by an admin (validate against `GET /api/v3/models` with a live key). The model a team can choose from is filtered to the team's `defaultMediaService()` (teams.default_media_provider, falls back to first connected media provider). New supported models = new `ai_models` rows (plus `.ai/rules/api-docs.md`), not config edits.

## Persona media storage: quota tracking + always-download service
All persona media writes/deletes go through `PersonaMediaService` (app/Services/Media): `storeFromUrl()` (always downloads — the path for non-wizard generated assets), `storeUploaded()` (refs), `delete()` (decrements usage), `assertWithinLimit()`, `reconcile()`. It maintains `teams.storage_used_bytes` (incremented on write, decremented on delete) and enforces `teams.storage_limit_bytes` (null = unlimited; set per team, e.g. via the teams settings page or future purchases) by throwing `StorageLimitExceededException`. The wizard still hotlinks generated images and only downloads the chosen one. Always route persona media storage through this service, never raw `Storage::disk('private')->put`.
