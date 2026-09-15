<?php

namespace App\Services\Ai\Clients;

use Laravel\Ai\Enums\Lab;

/**
 * A concrete LLM client (Claude, ChatGPT or DeepSeek) that holds the team's
 * credential and knows its provider/model, used by BackstoryAnalyst and
 * PromptEnhancer.
 */
interface LlmClient
{
    public function lab(): Lab;

    public function model(): string;

    /**
     * Inject the team's key into config/ai.php for the underlying provider.
     */
    public function configure(): void;

    /**
     * Return a plain text completion, or null on failure.
     */
    public function text(string $instructions, string $prompt, int $timeout = 120): ?string;
}
