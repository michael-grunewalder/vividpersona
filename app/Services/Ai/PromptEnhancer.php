<?php

namespace App\Services\Ai;

use App\Enums\ApiService;
use App\Models\Team;

final class PromptEnhancer
{
    /**
     * Enhance a prompt with the team's LLM client. Returns the original
     * prompt when no LLM is connected or the call fails.
     */
    public static function enhance(Team $team, string $prompt, ?ApiService $override = null): string
    {
        $client = LlmClientFactory::forTeam($team, $override);

        if ($client === null) {
            return $prompt;
        }

        $enhanced = $client->text(
            'You are a prompt engineer for photorealistic AI image generation. '
                .'Improve the given prompt while keeping its subject, pose, wardrobe, scene and style intent '
                .'exactly intact. Return only the improved prompt text.',
            $prompt,
            timeout: 120,
        );

        return $enhanced ?? $prompt;
    }
}
