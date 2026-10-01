# API Documentation

In this file you find links to the API endpoints in our supported APIs

## WaveSpeed
- General: https://wavespeed.ai/docs/rest-api
- Getting Started: https://wavespeed.ai/docs/get-started-api
- Live model catalog (for verifying `ai_models` endpoint ids): `GET https://api.wavespeed.ai/api/v3/models` (Bearer key)

## FAL.AI
- API Documentation: https://fal.ai/docs/documentation
- Async queue: `POST https://queue.fal.run/{model}` (header `Authorization: Key <key>`)

## Supported Image Models

The wizard's model list is the admin-curated `ai_models` table (see `.ai/rules/media.md`),
filtered to the team's default media provider. The `options` JSON carries each model's
size mapping (the 9:16 / 16:9 aspect ratio → the model's own parameter) and request defaults.

Seeded families (both FAL and WaveSpeed where available):

| Family | Example FAL endpoint | Example WaveSpeed model_id |
| --- | --- | --- |
| Qwen Image | `alibaba/qwen-image-3/text-to-image` | `alibaba/qwen-image-3/text-to-image` |
| WAN (text-to-image) | `fal-ai/wan/v2.7/text-to-image`, `.../v2.7/pro/...` | `wavespeed-ai/wan-2.1/text-to-image`, `alibaba/wan-2.7/text-to-image-pro` |
| Seedream | `fal-ai/bytedance/seedream/v4/text-to-image`, `bytedance/seedream/v5/{pro,lite}/text-to-image` | `bytedance/seedream-v5.0-pro` |
| GPT Image | `openai/gpt-image-2`, `openai/gpt-image-2.5/{flare,sunburst}/text-to-image` | `openai/gpt-image-2/text-to-image` |
| FLUX | `fal-ai/flux/{dev,schnell}` | `wavespeed-ai/flux-2-dev/text-to-image` |
| Ideogram | `ideogram/v4` | `ideogram-ai/ideogram-v3-balanced` |

Notes:
- WaveSpeed model ids change often — verify against `GET /api/v3/models` with a live key before trusting a seed row.
- GPT Image / WAN keep presets that satisfy each model's minimum-pixel constraint; some models use literal dimensions (width/height).
- Add any new supported model as an `ai_models` row via `/backend/ai-model`, not by editing config.