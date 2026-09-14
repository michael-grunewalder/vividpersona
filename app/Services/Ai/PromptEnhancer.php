<?php

namespace App\Services\Ai;

use App\Models\Team;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

final class PromptEnhancer
{
    /**
     * Enhance a prompt, preferring the provider's own enhancer when available.
     * WaveSpeed's REST enhancer endpoint is not yet exposed, so we fall back to
     * Claude via the team's LLM connection. Returns the original prompt on failure.
     */
    public static function enhance(Team $team, string $prompt): string
    {
        $key = BackstoryAnalyst::llmCredentials($team);

        if ($key === null) {
            return $prompt;
        }

        config(['ai.providers.anthropic.key' => $key]);

        try {
            return (string) agent(
                instructions: 'You are a prompt engineer for photorealistic AI image generation. '
                    .'Improve the given prompt while keeping its subject, pose, wardrobe, scene and style intent '
                    .'exactly intact. Return only the improved prompt text.',
            )->prompt($prompt, provider: Lab::Anthropic, model: 'claude-haiku-4-5-20251001', timeout: 120)->text;
        } catch (\Throwable) {
            return $prompt;
        }
    }
}
