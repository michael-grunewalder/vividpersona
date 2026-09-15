<?php

namespace App\Enums;

use Laravel\Ai\Enums\Lab;

/**
 * The external API services a team can connect. Media services generate
 * images; LLM services power backstory analysis and prompt enhancement.
 * Catalog metadata lives in config/services.php.
 */
enum ApiService: string
{
    case Fal = 'fal';
    case WaveSpeed = 'wavespeed';
    case Claude = 'claude';
    case ChatGpt = 'chatgpt';
    case DeepSeek = 'deepseek';

    public function label(): string
    {
        return (string) config("services.{$this->value}.label", $this->value);
    }

    public function type(): ApiServiceType
    {
        return config("services.{$this->value}.type") === ApiServiceType::Llm->value
            ? ApiServiceType::Llm
            : ApiServiceType::Media;
    }

    public function isMedia(): bool
    {
        return $this->type() === ApiServiceType::Media;
    }

    public function isLlm(): bool
    {
        return $this->type() === ApiServiceType::Llm;
    }

    /**
     * The credential field names this service requires.
     *
     * @return array<int, string>
     */
    public function credentialFields(): array
    {
        return ['api_key'];
    }

    /**
     * The default LLM model for this service (LLM services only).
     */
    public function defaultModel(): ?string
    {
        return config("services.{$this->value}.model");
    }

    /**
     * The AI SDK lab for this service (LLM services only).
     */
    public function lab(): ?Lab
    {
        $lab = config("services.{$this->value}.lab");

        return filled($lab) ? Lab::from($lab) : null;
    }

    /**
     * The config/ai.php provider key to inject the team's credential into.
     */
    public function aiConfigKey(): ?string
    {
        return config("services.{$this->value}.config_key");
    }

    /**
     * @return array<int, self>
     */
    public static function media(): array
    {
        return array_values(array_filter(self::cases(), fn (self $service): bool => $service->isMedia()));
    }

    /**
     * @return array<int, self>
     */
    public static function llm(): array
    {
        return array_values(array_filter(self::cases(), fn (self $service): bool => $service->isLlm()));
    }

    /**
     * @return array<int, string>
     */
    public static function mediaValues(): array
    {
        return array_map(fn (self $service): string => $service->value, self::media());
    }

    /**
     * @return array<int, string>
     */
    public static function llmValues(): array
    {
        return array_map(fn (self $service): string => $service->value, self::llm());
    }
}
