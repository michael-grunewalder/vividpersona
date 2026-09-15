# API Documentation

In this file you find links to the API endpoints in our supported APIs

## WaveSpeed
- General: https://wavespeed.ai/docs/rest-api
- Getting Started: https://wavespeed.ai/docs/get-started-api
- Live model catalog: `GET https://api.wavespeed.ai/api/v3/models` (Bearer key; filter `type === 'text-to-image'`)

## FAL.AI
- API Documentation: https://fal.ai/docs/documentation
- Async queue: `POST https://queue.fal.run/{model}` (header `Authorization: Key <key>`)

## Supported Image Models

Per-provider endpoint IDs. The wizard lists a provider's supported models and
maps the chosen aspect ratio (`9:16` / `16:9`) to each model's own size
parameter (see `app/Services/Media/MediaModelCatalog.php` and
`config/services.php`).

| Model | FAL.AI endpoint | WaveSpeed family |
| --- | --- | --- |
| Seedream 4 | `fal-ai/bytedance/seedream/v4/text-to-image` | seedream |
| Seedream 5 (Pro / Lite) | `bytedance/seedream/v5/pro/text-to-image`, `bytedance/seedream/v5/lite/text-to-image` | seedream |
| GPT Image 1.5 | `fal-ai/gpt-image-1.5` | gpt-image |
| GPT Image 2 | `openai/gpt-image-2` | gpt-image |
| GPT Image 2.5 (Flare / Sunburst) | `openai/gpt-image-2.5/flare/text-to-image`, `openai/gpt-image-2.5/sunburst/text-to-image` | gpt-image |
| Ideogram V4 | `ideogram/v4` | ideogram |
| Nano Banana (1 / 2) | `fal-ai/nano-banana`, `fal-ai/nano-banana-2` | nano-banana |
| Flux (dev / schnell) | `fal-ai/flux/dev`, `fal-ai/flux/schnell` | flux |

Notes:
- WaveSpeed uses its live model catalog (exact `model_id`s and request schema),
  filtered by the `model_families` keywords in `config/services.php`.
- GPT Image 2 / 2.5 use `image_size` presets kept at 4:3 to satisfy the
  model's minimum-pixel constraint; GPT Image 1.5 uses literal dimensions.