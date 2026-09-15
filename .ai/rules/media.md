---
paths:
  - 'app/Services/Media/**'
---

# Media

## Per-API model catalog: FAL curated, WaveSpeed live, payloads from spec
Image-model catalogs are per-API and model-specific. `MediaModelCatalog` (app/Services/Media) is the single source: FAL reads a curated spec from `config('services.fal.models')` (each entry = `[label, size=>[param,sizes], defaults]`); WaveSpeed fetches `GET /api/v3/models` live (Bearer key, cached 1h, filtered to `type=text-to-image` + the `model_families` keywords in config) and derives the size param (aspect_ratio / size / image_size / width_height) from each model's request schema. `MediaService::generateImages` builds the payload from that spec — never hardcode a size param per provider. Add any new supported model in BOTH config/services.php (FAL) and `.ai/rules/api-docs.md`, and (for WaveSpeed) ensure it matches a `model_families` keyword.
