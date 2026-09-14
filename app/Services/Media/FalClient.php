<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class FalClient
{
    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl = 'https://queue.fal.run',
    ) {}

    /**
     * Submit an async request to the fal queue and return the request id.
     */
    public function submit(string $model, array $input): string
    {
        $response = Http::withHeaders(['Authorization' => 'Key '.$this->key])
            ->post($this->baseUrl.'/'.$model, $input)
            ->throw()
            ->json();

        return $response['request_id'] ?? throw new RuntimeException('fal did not return a request id.');
    }

    /**
     * Poll the request until it completes and return the generated image urls.
     */
    public function waitForResult(string $model, string $requestId, int $maxAttempts = 120, int $delaySeconds = 3): array
    {
        $statusUrl = $this->baseUrl.'/'.$model.'/requests/'.$requestId.'/status';
        $resultUrl = $this->baseUrl.'/'.$model.'/requests/'.$requestId;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $status = Http::withHeaders(['Authorization' => 'Key '.$this->key])
                ->get($statusUrl)
                ->throw()
                ->json();

            $state = $status['status'] ?? 'IN_PROGRESS';

            if ($state === 'COMPLETED') {
                $result = Http::withHeaders(['Authorization' => 'Key '.$this->key])
                    ->get($resultUrl)
                    ->throw()
                    ->json();

                return collect($result['images'] ?? [])->pluck('url')->filter()->values()->all();
            }

            if (in_array($state, ['FAILED', 'CANCELLED', 'TIMEOUT'], true)) {
                throw new RuntimeException("fal request [{$requestId}] ended with status [{$state}].");
            }

            sleep($delaySeconds);
        }

        throw new RuntimeException("fal request [{$requestId}] timed out.");
    }
}
