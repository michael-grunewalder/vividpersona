<?php

namespace App\Jobs;

use App\Models\AiModel;
use App\Models\Persona;
use App\Services\Media\MediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

/**
 * Generates a single image for one prompt of an persona's generation set.
 * Kept small so it fits inside the worker's per-job time budget; the set is
 * marked ready when its last image has been appended.
 */
class GenerateImageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Persona $persona,
        public string $setId,
        public AiModel $aiModel,
        public string $aspectRatio,
        public string $prompt,
    ) {}

    public function handle(MediaService $media): void
    {
        $persona = $this->persona;
        $model = $this->aiModel;

        if ($model === null || ! $model->enabled) {
            $this->failPersona($persona, __('personas.job_error_model'));

            return;
        }

        $service = $model->provider;

        if (! $service->isMedia() || ! $persona->team->hasCredential($service)) {
            $this->failPersona($persona, __('personas.job_error_connection'));

            return;
        }

        $credentials = $persona->team->credentialsFor($service);

        try {
            $urls = $media->generateImages($model, $credentials, $this->prompt, $this->aspectRatio);

            $image = ['id' => (string) Str::ulid(), 'url' => $urls[0] ?? null, 'prompt' => $this->prompt, 'model' => $model->label()];
        } catch (Throwable $e) {
            report($e);

            $image = ['id' => (string) Str::ulid(), 'url' => null, 'prompt' => $this->prompt, 'error' => $this->message($e), 'model' => $model->label()];
        }

        $this->appendImage($persona, $image);
    }

    /**
     * @param  array<string, mixed>  $image
     */
    private function appendImage(Persona $persona, array $image): void
    {
        $persona->refresh();

        $history = $persona->generation_history ?? [];
        $setIndex = null;
        $total = 0;

        foreach ($history as $index => $set) {
            if (($set['id'] ?? null) === $this->setId) {
                $history[$index]['images'][] = $image;
                $setIndex = $index;
                $total = (int) ($set['total'] ?? count($history[$index]['images']));
                break;
            }
        }

        if ($setIndex === null) {
            return;
        }

        $done = count($history[$setIndex]['images']);

        if ($done >= $total) {
            $history[$setIndex]['status'] = 'ready';

            $persona->update([
                'generation_history' => $history,
                'status' => 'ready',
                'last_error' => null,
            ]);

            return;
        }

        $persona->update(['generation_history' => $history]);
    }

    private function failPersona(Persona $persona, string $reason): void
    {
        $persona->update(['status' => 'failed', 'last_error' => $reason]);
    }

    private function message(Throwable $e): string
    {
        return mb_strimwidth($e->getMessage() ?: $e::class, 0, 200, '…');
    }
}
