<?php

namespace App\Services\Connections;

use App\Enums\ApiService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final class ApiCredentialVerifier
{
    private const TIMEOUT = 10;

    /**
     * Verify an API key against the service's account endpoint.
     */
    public function verify(ApiService $service, string $apiKey): bool
    {
        try {
            return match ($service) {
                ApiService::Fal => $this->fal($apiKey),
                ApiService::WaveSpeed => $this->wavespeed($apiKey),
                ApiService::Claude => $this->claude($apiKey),
                ApiService::ChatGpt => $this->chatgpt($apiKey),
                ApiService::DeepSeek => $this->deepseek($apiKey),
            };
        } catch (Throwable) {
            return false;
        }
    }

    private function fal(string $apiKey): bool
    {
        return $this->get(config('services.fal.validate_url'), ['Authorization' => 'Key '.$apiKey])->successful();
    }

    private function wavespeed(string $apiKey): bool
    {
        $response = $this->request(config('services.wavespeed.validate_url'), $apiKey);

        return $response->successful() && data_get($response->json(), 'code') === 200;
    }

    private function claude(string $apiKey): bool
    {
        return $this->request(config('services.claude.validate_url'), $apiKey, [
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->successful();
    }

    private function chatgpt(string $apiKey): bool
    {
        return $this->request(config('services.chatgpt.validate_url'), $apiKey)->successful();
    }

    private function deepseek(string $apiKey): bool
    {
        return $this->request(config('services.deepseek.validate_url'), $apiKey)->successful();
    }

    private function get(string $url, array $headers): Response
    {
        return Http::withHeaders($headers)->timeout(self::TIMEOUT)->get($url);
    }

    private function request(string $url, string $apiKey, array $headers = []): Response
    {
        return Http::withToken($apiKey)->withHeaders($headers)->timeout(self::TIMEOUT)->get($url);
    }
}
