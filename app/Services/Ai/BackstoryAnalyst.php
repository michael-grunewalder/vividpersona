<?php

namespace App\Services\Ai;

use App\Enums\ApiProviderType;
use App\Models\ApiProvider;
use App\Models\Team;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Anthropic)]
#[Model('claude-haiku-4-5-20251001')]
#[MaxTokens(200)]
class BackstoryAnalyst implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You analyze an AI influencer backstory for a photo prompt engine. Return only JSON '
            .'with a "sceneNiche" (one of: fashion, beauty, lifestyle, fitness, travel, tech, gaming, entertainment) '
            .'and "tags" (an array of strings from: quiet, minimalist, clean, structured, editorial, casual, urban, street, dark, '
            .'moody, earthy, natural, bohemian, cottagecore, y2k, playful, nostalgic, glam, bold, evening, sport, functional, '
            .'active, preppy, classic, polished, old-money, coastal).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sceneNiche' => $schema->string()->required(),
            'tags' => $schema->array($schema->string()),
        ];
    }

    /**
     * Analyze the backstory with the team's LLM connection, or return null.
     *
     * @return array{sceneNiche: string, tags: array<int, string>}|null
     */
    public static function analyze(Team $team, string $backstory, string $physicalDesc): ?array
    {
        $credentials = self::llmCredentials($team);

        if ($credentials === null) {
            return null;
        }

        config(['ai.providers.anthropic.key' => $credentials]);

        try {
            $response = (new static)->prompt("Backstory: {$backstory}\n\nPhysical description: {$physicalDesc}");

            return [
                'sceneNiche' => $response['sceneNiche'] ?? 'lifestyle',
                'tags' => $response['tags'] ?? [],
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The team's Anthropic key, or null when the team has no LLM connection.
     */
    public static function llmCredentials(Team $team): ?string
    {
        $provider = ApiProvider::query()
            ->where('type', ApiProviderType::Llm)
            ->orderByRaw("machine_name = 'claude' DESC, machine_name = 'anthropic' DESC")
            ->first();

        $key = $provider ? ($team->providerCredentials($provider)['api_key'] ?? null) : null;

        return filled($key) ? (string) $key : null;
    }
}
