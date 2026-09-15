<?php

namespace App\Services\Ai;

use App\Enums\ApiService;
use App\Models\Persona;
use App\Support\Prompt\PersonaPromptBuilder;

final class PersonaPromptService
{
    /**
     * Build the three variation prompts for an persona, optionally applying
     * backstory analysis and LLM prompt enhancement.
     *
     * @return array<int, string>
     */
    public function build(Persona $persona, string $model, string $aspectRatio, bool $enhance = false, ?string $llmProvider = null): array
    {
        $team = $persona->team;
        $override = filled($llmProvider) ? ApiService::tryFrom($llmProvider) : null;

        $promptData = $persona->promptData();

        if (filled(trim((string) $persona->backstory))) {
            $analysis = BackstoryAnalyst::analyze(
                $team,
                (string) $persona->backstory,
                (string) $persona->physical_desc,
                $override,
            );

            if ($analysis !== null) {
                $promptData['backstoryContext'] = $analysis;
            }
        }

        $prompts = PersonaPromptBuilder::buildThreeVariationPrompts($promptData, $aspectRatio, $model);

        if ($enhance) {
            $prompts = array_map(
                fn (string $prompt) => PromptEnhancer::enhance($team, $prompt, $override),
                $prompts,
            );
        }

        return $prompts;
    }
}
