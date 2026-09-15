<?php

namespace App\Services\Ai\Clients;

use Throwable;

use function Laravel\Ai\agent;

/**
 * Shared behaviour for the Laravel AI SDK backed clients.
 */
abstract class AbstractLlmClient implements LlmClient
{
    public function __construct(protected string $apiKey) {}

    /**
     * The config/ai.php provider key to inject the credential into.
     */
    abstract protected function configKey(): string;

    public function configure(): void
    {
        config(["ai.providers.{$this->configKey()}.key" => $this->apiKey]);
    }

    public function text(string $instructions, string $prompt, int $timeout = 120): ?string
    {
        try {
            return (string) agent(instructions: $instructions)
                ->prompt($prompt, provider: $this->lab(), model: $this->model(), timeout: $timeout)->text;
        } catch (Throwable) {
            return null;
        }
    }
}
