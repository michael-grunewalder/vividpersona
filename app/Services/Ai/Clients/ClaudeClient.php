<?php

namespace App\Services\Ai\Clients;

use Laravel\Ai\Enums\Lab;

final class ClaudeClient extends AbstractLlmClient
{
    public function lab(): Lab
    {
        return Lab::Anthropic;
    }

    public function model(): string
    {
        return (string) (config('services.claude.model') ?? 'claude-haiku-4-5-20251001');
    }

    protected function configKey(): string
    {
        return 'anthropic';
    }
}
