<?php

namespace App\Services\Ai;

use App\Enums\ApiService;
use App\Models\Team;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[MaxTokens(200)]
class BackstoryAnalyst implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You analyze an AI persona backstory for a photo prompt engine. Return only JSON '
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
     * Analyze the backstory with the team's LLM client, or return null.
     *
     * @return array{sceneNiche: string, tags: array<int, string>}|null
     */
    public static function analyze(Team $team, string $backstory, string $physicalDesc, ?ApiService $override = null): ?array
    {
        $client = LlmClientFactory::forTeam($team, $override);

        if ($client === null) {
            return null;
        }

        $client->configure();

        try {
            $response = (new static)->prompt(
                "Backstory: {$backstory}\n\nPhysical description: {$physicalDesc}",
                provider: $client->lab(),
                model: $client->model(),
            );

            return [
                'sceneNiche' => $response['sceneNiche'] ?? 'lifestyle',
                'tags' => $response['tags'] ?? [],
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
