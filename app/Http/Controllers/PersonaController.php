<?php

namespace App\Http\Controllers;

use App\Enums\ApiService;
use App\Jobs\GenerateImageJob;
use App\Models\Persona;
use App\Models\Team;
use App\Services\Ai\PersonaPromptService;
use App\Services\Media\MediaModelCatalog;
use App\Support\PersonaOptions;
use App\Support\Prompt\PersonaPromptBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
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
            'providers' => $this->mediaProviders($team),
            'llmProviders' => $this->llmProviders($team),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $team = $this->team($request);

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
            'provider' => ['required', Rule::in(ApiService::mediaValues())],
            'model' => ['required', 'string', 'max:255'],
            'llm_provider' => ['nullable', Rule::in(ApiService::llmValues())],
            'face_ref' => ['nullable', 'image', 'max:4096'],
            'style_ref' => ['nullable', 'image', 'max:4096'],
        ]);

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
            'provider' => $validated['provider'],
            'model' => $validated['model'],
            'llm_provider' => $validated['llm_provider'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->hasFile('face_ref')) {
            $file = $request->file('face_ref');
            $persona->update(['face_ref_path' => $file->storeAs($persona->mediaFolder(), 'face-ref-'.Str::ulid().'.'.$file->extension(), 'private')]);
        }

        if ($request->hasFile('style_ref')) {
            $file = $request->file('style_ref');
            $persona->update(['style_ref_path' => $file->storeAs($persona->mediaFolder(), 'style-ref-'.Str::ulid().'.'.$file->extension(), 'private')]);
        }

        $persona->update(['physical_desc' => PersonaPromptBuilder::physicalDesc($persona->promptData())]);

        $this->startGeneration(
            $persona,
            $validated['provider'],
            $validated['model'],
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
            'providers' => $this->mediaProviders($team),
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

        $validated = $request->validate([
            'provider' => ['required', Rule::in(ApiService::mediaValues())],
            'model' => ['required', 'string', 'max:255'],
            'aspect_ratio' => ['required', 'in:9:16,16:9'],
            'llm_provider' => ['nullable', Rule::in(ApiService::llmValues())],
        ]);

        $this->startGeneration(
            $persona,
            $validated['provider'],
            $validated['model'],
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

        $path = $this->storeMediaFromUrl($persona, (string) $selected['url']);

        $this->clearStoredMedia($persona);

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
     * Download a remote image into the persona's private media folder and
     * return the stored relative path.
     */
    private function storeMediaFromUrl(Persona $persona, string $url): string
    {
        $response = Http::timeout(30)->get($url);

        if ($response->failed()) {
            abort(422, __('personas.image_download_failed'));
        }

        $contentType = (string) $response->header('Content-Type');
        $extension = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            default => 'jpeg',
        };

        $path = $persona->mediaFolder().'/'.(string) Str::ulid().'.'.$extension;

        Storage::disk('private')->put($path, $response->body());

        return $path;
    }

    /**
     * Delete the persona's previously stored local media files.
     */
    private function clearStoredMedia(Persona $persona): void
    {
        foreach (array_filter([$persona->main_image, $persona->reference_image_path, $persona->face_ref_path, $persona->style_ref_path]) as $path) {
            if (is_string($path) && ! str_contains($path, '://') && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }
        }
    }

    public function destroy(Request $request, Persona $persona): RedirectResponse
    {
        $this->authorizeTeam($persona);

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
     * image job per prompt.
     */
    private function startGeneration(Persona $persona, string $provider, string $model, string $aspectRatio, bool $enhance = false, ?string $llmProvider = null): void
    {
        $prompts = app(PersonaPromptService::class)->build($persona, $model, $aspectRatio, $enhance, $llmProvider);

        $setId = (string) Str::ulid();

        $persona->update([
            'status' => 'generating',
            'generation_history' => array_merge($persona->generation_history ?? [], [[
                'id' => $setId,
                'provider' => $provider,
                'model' => $model,
                'aspect_ratio' => $aspectRatio,
                'status' => 'generating',
                'images' => [],
                'total' => count($prompts),
                'created_at' => now()->toISOString(),
            ]]),
        ]);

        foreach ($prompts as $prompt) {
            GenerateImageJob::dispatch($persona, $setId, $provider, $model, $aspectRatio, $prompt);
        }
    }

    /**
     * The media services the team has connected, with their model catalogs.
     *
     * @return Collection<int, array{service: ApiService, models: array<string, string>}>
     */
    private function mediaProviders(Team $team): Collection
    {
        $catalog = app(MediaModelCatalog::class);

        return collect(ApiService::media())
            ->filter(fn (ApiService $service) => $team->hasCredential($service))
            ->map(fn (ApiService $service) => [
                'service' => $service,
                'models' => $catalog->models($service, $team->credentialFor($service)?->apiKey())->all(),
            ])
            ->filter(fn (array $item) => $item['models'] !== [])
            ->values();
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
