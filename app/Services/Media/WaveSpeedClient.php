<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class WaveSpeedClient
{
    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl = 'https://api.wavespeed.ai',
    ) {}

    /**
     * Submit a task and return the task id.
     */
    public function submit(string $model, array $input): string
    {
        $response = Http::withToken($this->key)
            ->post($this->baseUrl.'/api/v3/'.$model, $input)
            ->throw()
            ->json();

        return $response['data']['id'] ?? throw new RuntimeException('WaveSpeed did not return a task id.');
    }

    /**
     * Poll the task until it completes and return the output urls.
     */
    public function waitForResult(string $taskId, int $maxAttempts = 120, int $delaySeconds = 3): array
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $data = Http::withToken($this->key)
                ->get($this->baseUrl.'/api/v3/predictions/'.$taskId.'/result')
                ->throw()
                ->json()['data'] ?? [];

            $status = $data['status'] ?? 'processing';

            if ($status === 'completed') {
                return collect($data['outputs'] ?? [])->filter()->values()->all();
            }

            if (in_array($status, ['failed', 'cancelled', 'timeout', 'deleted'], true)) {
                throw new RuntimeException("WaveSpeed task [{$taskId}] ended with status [{$status}].");
            }

            sleep($delaySeconds);
        }

        throw new RuntimeException("WaveSpeed task [{$taskId}] timed out.");
    }
}
