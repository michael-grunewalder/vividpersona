<?php

namespace App\Services\Ai\Clients;

use Laravel\Ai\Enums\Lab;

final class ChatGptClient extends AbstractLlmClient
{
    public function lab(): Lab
    {
        return Lab::OpenAI;
    }

    public function model(): string
    {
        return (string) (config('services.chatgpt.model') ?? 'gpt-4o-mini');
    }

    protected function configKey(): string
    {
        return 'openai';
    }
}
