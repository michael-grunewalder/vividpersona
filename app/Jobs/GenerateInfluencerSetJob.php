<?php

namespace App\Jobs;

use App\Models\ApiProvider;
use App\Models\Influencer;
use App\Services\Ai\PromptEnhancer;
use App\Services\Media\MediaService;
use App\Support\Prompt\InfluencerPromptBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class GenerateInfluencerSetJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>|null  $prompts
     */
    public function __construct(
        public Influencer $influencer,
        public string $provider,
        public string $model,
        public string $aspectRatio = '9:16',
        public ?array $prompts = null,
        public bool $enhance = false,
    ) {}

    /**
     * Generate one set of avatar variations and append it to the influencer.
     */
    public function handle(MediaService $media): void
    {
        $influencer = $this->influencer;

        $influencer->update(['status' => 'generating']);

        $provider = ApiProvider::query()->where('machine_name', $this->provider)->first();

        if (! $provider) {
            $influencer->update(['status' => 'failed']);

            return;
        }

        $credentials = $influencer->team->providerCredentials($provider);

        $prompts = $this->prompts
            ?? InfluencerPromptBuilder::buildThreeVariationPrompts($influencer->promptData(), $this->aspectRatio);

        if ($this->enhance) {
            $prompts = array_map(
                fn (string $prompt) => PromptEnhancer::enhance($influencer->team, $prompt),
                $prompts,
            );
        }

        $images = [];

        foreach ($prompts as $prompt) {
            try {
                $urls = $media->generateImages($provider, $credentials, $this->model, $prompt, $this->aspectRatio);
                $url = $urls[0] ?? null;
            } catch (\Throwable $e) {
                report($e);
                $url = null;
            }

            $images[] = ['id' => (string) Str::ulid(), 'url' => $url, 'prompt' => $prompt];
        }

        $history = $influencer->generation_history ?? [];
        $history[] = [
            'id' => (string) Str::ulid(),
            'provider' => $this->provider,
            'model' => $this->model,
            'aspect_ratio' => $this->aspectRatio,
            'status' => 'ready',
            'images' => $images,
            'created_at' => now()->toISOString(),
        ];

        $influencer->update([
            'generation_history' => $history,
            'status' => 'ready',
        ]);
    }
}
