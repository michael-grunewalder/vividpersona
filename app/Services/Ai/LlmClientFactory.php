<?php

namespace App\Services\Ai;

use App\Enums\ApiService;
use App\Models\Team;
use App\Services\Ai\Clients\ChatGptClient;
use App\Services\Ai\Clients\ClaudeClient;
use App\Services\Ai\Clients\DeepSeekClient;
use App\Services\Ai\Clients\LlmClient;
use InvalidArgumentException;

final class LlmClientFactory
{
    /**
     * Build a client for the given LLM service with the given API key.
     */
    public static function make(ApiService $service, string $apiKey): LlmClient
    {
        return match ($service) {
            ApiService::Claude => new ClaudeClient($apiKey),
            ApiService::ChatGpt => new ChatGptClient($apiKey),
            ApiService::DeepSeek => new DeepSeekClient($apiKey),
            default => throw new InvalidArgumentException("Service [{$service->value}] is not an LLM provider."),
        };
    }

    /**
     * Resolve the client for a team: the given override when it is an LLM
     * service, otherwise the team's default LLM. Returns null when the team
     * has no usable LLM credential.
     */
    public static function forTeam(Team $team, ?ApiService $override = null): ?LlmClient
    {
        $service = $override?->isLlm() ? $override : $team->defaultLlmService();

        $key = $service ? $team->credentialFor($service)?->apiKey() : null;

        if (! filled($key)) {
            return null;
        }

        return self::make($service, (string) $key);
    }
}
