<?php

namespace App\Http\Controllers;

use App\Enums\AiModelType;
use App\Enums\ApiService;
use App\Enums\GenerationMode;
use App\Exceptions\StorageLimitExceededException;
use App\Jobs\GenerateImageJob;
use App\Models\AiModel;
use App\Models\Persona;
use App\Models\Team;
use App\Services\Ai\PersonaPromptService;
use App\Services\Media\AiModelCatalog;
use App\Services\Media\PersonaMediaService;
use App\Support\PersonaOptions;
use App\Support\Prompt\PersonaPromptBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonaController extends Controller
{
    public function index(Request $request): View
    {
        $personas = Persona::query()
            ->where('team_id', $this->team($request)->getKey())
            ->latest()
            ->get();

        return view('personas.index', ['personas' => $personas]);
    }

    public function create(Request $request): View
    {
        $team = $this->team($request);

        return view('personas.create', [
            'options' => PersonaOptions::class,
            'models' => $this->imageModels($team),
            'defaultModel' => $this->defaultImageModel($team)?->label(),
            'llmProviders' => $this->llmProviders($team),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $team = $this->team($request);
        $mode = (string) $request->input('mode');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Female,Male'],
            'age' => ['required', 'integer', 'min:18'],
            'niches' => ['nullable', 'array'],
            'niche_custom' => ['nullable', 'string', 'max:255'],
            'backstory' => ['nullable', 'string'],
            'personality' => ['required', 'integer', 'between:0,100'],
            'ethnicity' => ['nullable', 'string', 'max:255'],
            'skin_tone' => ['nullable', 'string', 'max:255'],
            'hair_color' => ['nullable', 'string', 'max:255'],
            'hair_length' => ['nullable', 'string', 'max:255'],
            'hair_texture' => ['nullable', 'string', 'max:255'],
            'eye_color' => ['nullable', 'string', 'max:255'],
            'build' => ['nullable', 'string', 'max:255'],
            'unique_features' => ['nullable', 'string', 'max:255'],
            'vibe_words' => ['nullable', 'string', 'max:255'],
            'aspect_ratio' => ['required', 'in:9:16,16:9'],
            'mode' => ['required', Rule::enum(GenerationMode::class)],
            'ai_model_id' => ['nullable', 'string', 'max:26', Rule::exists('ai_models', 'id'), Rule::requiredIf($mode === 'custom')],
            'ai_model_ids' => Rule::when($mode === 'three_models', ['required', 'array', 'size:3'], ['nullable', 'array']),
            'ai_model_ids.*' => ['required', 'string', 'max:26', Rule::exists('ai_models', 'id')],
            'llm_provider' => ['nullable', Rule::in(ApiService::llmValues())],
            'face_ref' => ['nullable', 'image', 'max:4096'],
            'style_ref' => ['nullable', 'image', 'max:4096'],
        ]);

        $models = $this->resolveGenerationModels($request, $team, GenerationMode::from($validated['mode']));
        $model = $models[0];

        $persona = Persona::query()->create([
            'team_id' => $team->getKey(),
            'owner_id' => $request->user()->getKey(),
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'age' => $validated['age'],
            'niches' => $validated['niches'] ?? [],
            'niche_custom' => $validated['niche_custom'] ?? null,
            'backstory' => $validated['backstory'] ?? null,
            'personality' => $validated['personality'],
            'ethnicity' => $validated['ethnicity'] ?? null,
            'skin_tone' => $validated['skin_tone'] ?? null,
            'hair_color' => $validated['hair_color'] ?? null,
            'hair_length' => $validated['hair_length'] ?? null,
            'hair_texture' => $validated['hair_texture'] ?? null,
            'eye_color' => $validated['eye_color'] ?? null,
            'build' => $validated['build'] ?? null,
            'unique_features' => $validated['unique_features'] ?? null,
            'vibe_words' => filled($validated['vibe_words'] ?? null) ? [$validated['vibe_words']] : [],
            'aspect_ratio' => $validated['aspect_ratio'],
            'ai_model_id' => $model->getKey(),
            'provider' => $model->provider->value,
            'model' => $model->endpoint,
            'llm_provider' => $validated['llm_provider'] ?? null,
            'status' => 'pending',
        ]);

        try {
            if ($request->hasFile('face_ref')) {
                $persona->update(['face_ref_path' => app(PersonaMediaService::class)->storeUploaded($persona, $request->file('face_ref'), 'face-ref')]);
            }

            if ($request->hasFile('style_ref')) {
                $persona->update(['style_ref_path' => app(PersonaMediaService::class)->storeUploaded($persona, $request->file('style_ref'), 'style-ref')]);
            }
        } catch (StorageLimitExceededException) {
            $persona->delete();

            return back()->withErrors(['storage' => __('personas.storage_limit')])->withInput();
        }

        $persona->update(['physical_desc' => PersonaPromptBuilder::physicalDesc($persona->promptData())]);

        $this->startGeneration(
            $persona,
            GenerationMode::from($validated['mode']),
            $models,
            $validated['aspect_ratio'],
            enhance: $request->boolean('enhance'),
            llmProvider: $validated['llm_provider'] ?? null,
        );

        return redirect()->route('personas.show', $persona);
    }

    public function show(Request $request, Persona $persona): View
    {
        $this->authorizeTeam($persona);

        $team = $this->team($request);

        return view('personas.show', [
            'persona' => $persona,
            'models' => $this->imageModels($team),
            'defaultModel' => $this->defaultImageModel($team)?->label(),
            'llmProviders' => $this->llmProviders($team),
        ]);
    }

    public function status(Request $request, Persona $persona): JsonResponse
    {
        $this->authorizeTeam($persona);

        return response()->json([
            'status' => $persona->status,
            'sets' => $persona->generation_history ?? [],
            'last_error' => $persona->last_error,
        ]);
    }

    public function generateSet(Request $request, Persona $persona): RedirectResponse
    {
        $this->authorizeTeam($persona);

        if ($persona->reference_image_path !== null) {
            return back()->withErrors(['generation' => __('personas.already_chosen')]);
        }

        $team = $persona->team;
        $mode = (string) $request->input('mode');

        $validated = $request->validate([
            'mode' => ['required', Rule::enum(GenerationMode::class)],
            'ai_model_id' => ['nullable', 'string', 'max:26', Rule::exists('ai_models', 'id'), Rule::requiredIf($mode === 'custom')],
            'ai_model_ids' => Rule::when($mode === 'three_models', ['required', 'array', 'size:3'], ['nullable', 'array']),
            'ai_model_ids.*' => ['required', 'string', 'max:26', Rule::exists('ai_models', 'id')],
            'aspect_ratio' => ['required', 'in:9:16,16:9'],
            'llm_provider' => ['nullable', Rule::in(ApiService::llmValues())],
        ]);

        $models = $this->resolveGenerationModels($request, $team, GenerationMode::from($validated['mode']));

        $this->startGeneration(
            $persona,
            GenerationMode::from($validated['mode']),
            $models,
            $validated['aspect_ratio'],
            enhance: false,
            llmProvider: $validated['llm_provider'] ?? null,
        );

        return back();
    }

    public function choose(Request $request, Persona $persona): RedirectResponse
    {
        $this->authorizeTeam($persona);

        $request->validate(['image_id' => ['required', 'string']]);

        $selected = null;

        foreach (($persona->generation_history ?? []) as $set) {
            foreach ($set['images'] ?? [] as $image) {
                if (($image['id'] ?? null) === $request->image_id) {
                    $selected = $image;
                    break 2;
                }
            }
        }

        if ($selected === null) {
            return back()->withErrors(['image_id' => __('personas.image_not_found')]);
        }

        if ($persona->reference_image_path !== null || $persona->main_image !== null) {
            return back()->withErrors(['image_id' => __('personas.already_chosen')]);
        }

        if (! filled($selected['url'] ?? null)) {
            return back()->withErrors(['image_id' => __('personas.image_download_failed')]);
        }

        $media = app(PersonaMediaService::class);

        try {
            $path = $media->storeFromUrl($persona, (string) $selected['url']);
        } catch (StorageLimitExceededException) {
            return back()->withErrors(['image_id' => __('personas.storage_limit')]);
        }

        $this->clearStoredMedia($persona, $media);

        $persona->update([
            'main_image' => $path,
            'reference_image_path' => $path,
            'prompt' => $selected['prompt'] ?? null,
            'generation_history' => [],
            'face_ref_path' => null,
            'style_ref_path' => null,
        ]);

        return redirect()->route('personas.show', $persona)->with('success', __('personas.chosen'));
    }

    /**
     * Delete the persona's previously stored local media files.
     */
    private function clearStoredMedia(Persona $persona, PersonaMediaService $media): void
    {
        $team = $persona->team;

        foreach (array_filter([$persona->main_image, $persona->reference_image_path, $persona->face_ref_path, $persona->style_ref_path]) as $path) {
            if (is_string($path) && ! str_contains($path, '://')) {
                $media->delete($team, $path);
            }
        }
    }

    public function destroy(Request $request, Persona $persona): RedirectResponse
    {
        $this->authorizeTeam($persona);

        $team = $persona->team;
        $media = app(PersonaMediaService::class);

        foreach (Storage::disk('private')->allFiles($persona->mediaFolder()) as $file) {
            $media->delete($team, $file);
        }

        $persona->delete();

        return redirect()->route('personas.index')->with('success', __('personas.deleted', ['name' => $persona->name]));
    }

    private function team(Request $request): Team
    {
        return $request->user()->currentTeam();
    }

    private function authorizeTeam(Persona $persona): void
    {
        abort_unless($persona->team_id === $this->team(request())?->getKey(), 404);
    }

    /**
     * Build the variation prompts, create a new generation set and dispatch one
     * image job per (model, prompt) pair.
     *
     * @param  array<int, AiModel>  $models
     */
    private function startGeneration(Persona $persona, GenerationMode $mode, array $models, string $aspectRatio, bool $enhance = false, ?string $llmProvider = null): void
    {
        $prompts = app(PersonaPromptService::class)->build($persona, $models[0]->endpoint, $aspectRatio, $enhance, $llmProvider);

        $plan = match ($mode) {
            GenerationMode::ThreeModels => array_map(
                fn (AiModel $model, int $index): array => ['model' => $model, 'prompt' => $prompts[$index]],
                $models,
                array_keys($prompts),
            ),
            default => array_map(fn (string $prompt): array => ['model' => $models[0], 'prompt' => $prompt], $prompts),
        };

        $setId = (string) Str::ulid();

        $persona->update([
            'status' => 'generating',
            'generation_history' => array_merge($persona->generation_history ?? [], [[
                'id' => $setId,
                'mode' => $mode->value,
                'models' => collect($models)->map(fn (AiModel $model): array => [
                    'ai_model_id' => $model->getKey(),
                    'provider' => $model->provider->value,
                    'endpoint' => $model->endpoint,
                    'label' => $model->label(),
                ])->all(),
                'aspect_ratio' => $aspectRatio,
                'status' => 'generating',
                'images' => [],
                'total' => count($plan),
                'created_at' => now()->toISOString(),
            ]]),
        ]);

        foreach ($plan as $item) {
            GenerateImageJob::dispatch($persona, $setId, $item['model'], $aspectRatio, $item['prompt']);
        }
    }

    /**
     * The image model catalog for the team's default media provider.
     *
     * @return array<int, array{key: string, label: string, versions: array<int, array{key: string, label: string, variants: array<int, array{key: string, label: string, id: string}>}>}>
     */
    private function imageModels(Team $team): array
    {
        $provider = $team->defaultMediaService();

        return $provider ? app(AiModelCatalog::class)->tree(AiModelType::Image, $provider) : [];
    }

    /**
     * The team's default image model, used by the default generation mode.
     */
    private function defaultImageModel(Team $team): ?AiModel
    {
        $provider = $team->defaultMediaService();

        return $provider ? app(AiModelCatalog::class)->defaultFor(AiModelType::Image, $provider) : null;
    }

    /**
     * Resolve the models for a generation mode, verifying each is an enabled
     * image model offered by the team's default media provider.
     *
     * @return array<int, AiModel>
     */
    private function resolveGenerationModels(Request $request, Team $team, GenerationMode $mode): array
    {
        $models = match ($mode) {
            GenerationMode::Default => [$this->defaultImageModel($team)],
            GenerationMode::Custom => [$this->findImageModelForTeam((string) $request->input('ai_model_id'), $team)],
            GenerationMode::ThreeModels => array_map(
                fn (string $id): AiModel => $this->findImageModelForTeam($id, $team),
                (array) $request->input('ai_model_ids'),
            ),
        };

        abort_unless(count($models) > 0 && ! in_array(null, $models, true), 422);

        return array_values($models);
    }

    /**
     * Load a model and verify it is an enabled image model for the team's
     * default media provider.
     */
    private function findImageModelForTeam(string $id, Team $team): AiModel
    {
        $model = app(AiModelCatalog::class)->find($id);

        $valid = $model !== null
            && $model->type === AiModelType::Image
            && $model->provider === $team->defaultMediaService();

        abort_unless($valid, 422);

        return $model;
    }

    /**
     * The LLM services the team has connected, for the AI provider override.
     *
     * @return Collection<int, ApiService>
     */
    private function llmProviders(Team $team): Collection
    {
        return collect(ApiService::llm())
            ->filter(fn (ApiService $service) => $team->hasCredential($service))
            ->values();
    }
}
