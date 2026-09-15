<?php

namespace App\Services\Ai\Clients;

use Laravel\Ai\Enums\Lab;

final class DeepSeekClient extends AbstractLlmClient
{
    public function lab(): Lab
    {
        return Lab::DeepSeek;
    }

    public function model(): string
    {
        return (string) (config('services.deepseek.model') ?? 'deepseek-chat');
    }

    protected function configKey(): string
    {
        return 'deepseek';
    }
}
