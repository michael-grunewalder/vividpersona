---
paths:
  - app/Jobs/GenerateImageJob.php
  - 'app/Jobs/**'
---

# Jobs

## Progressive image generation + last_error
Generation is progressive: the job first appends a `generation_history` set with `status: 'generating'` and empty `images`, then appends each image (`{id, url, prompt[, error]}`) as it completes (refresh the model from DB before each write to survive concurrent sets), then marks the set and persona `ready`. Early failures (unknown provider / missing credential) set `status: 'failed'` and a short `personas.last_error` message so the show page can explain. The show page polls `personas.status` every 4s and re-renders the sets live via Alpine — no page reloads until a terminal status.

## One image per queued job (60s --once worker cap)
The deployed host runs `php artisan queue:work --once` with a 60s per-process timeout and `--tries=1`, so any single job must finish in ~60s. Never put multiple slow image generations in one job. Generation is split: `PersonaPromptService::build()` produces the 3 prompts (in the request), the controller appends a `generation_history` set placeholder (`status: generating`, `total`, empty `images`), then dispatches ONE `GenerateImageJob` per prompt. Each job appends its image to the set (refresh the model first) and marks the set+persona `ready` when `count(images) >= total`. The show page polls status every 4s and stops after 5 minutes (stall notice) — never an endless spinner.

## Queue worker timeout is VPS-side; keep one image per job
The queue worker runs on the VPS via an external crontab/systemd unit (`php artisan queue:work --once`), NOT from the repo — there is no scheduler definition in code. The worker's per-job budget is governed by the cron wrapper timeout AND Laravel's `--timeout` flag; if jobs are killed at 60s, raise `--timeout` (e.g. `--timeout=300`) and the wrapper timeout on the VPS. Image generation stays one-image-per-job (`GenerateImageJob`) so results stream in as they complete; each job appends its image to the set and marks it `ready` when `count(images) >= total`.
