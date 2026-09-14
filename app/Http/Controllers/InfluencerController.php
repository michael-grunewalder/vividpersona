<?php

namespace App\Http\Controllers;

use App\Enums\ApiProviderType;
use App\Jobs\GenerateInfluencerSetJob;
use App\Models\ApiProvider;
use App\Models\Influencer;
use App\Models\Team;
use App\Support\InfluencerOptions;
use App\Support\Prompt\InfluencerPromptBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfluencerController extends Controller
{
    public function index(Request $request): View
    {
        $influencers = Influencer::query()
            ->where('team_id', $this->team($request)->getKey())
            ->latest()
            ->get();

        return view('influencers.index', ['influencers' => $influencers]);
    }

    public function create(Request $request): View
    {
        return view('influencers.create', [
            'options' => InfluencerOptions::class,
            'providers' => $this->mediaProviders($this->team($request)),
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
            'provider' => ['required', 'string', Rule::exists('api_providers', 'machine_name')->where('type', 'media')],
            'model' => ['required', 'string', 'max:255'],
            'face_ref' => ['nullable', 'image', 'max:4096'],
            'style_ref' => ['nullable', 'image', 'max:4096'],
        ]);

        $influencer = Influencer::query()->create([
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
            'status' => 'pending',
        ]);

        if ($request->hasFile('face_ref')) {
            $influencer->update(['face_ref_path' => $request->file('face_ref')->store('influencer-refs', 'public')]);
        }

        if ($request->hasFile('style_ref')) {
            $influencer->update(['style_ref_path' => $request->file('style_ref')->store('influencer-refs', 'public')]);
        }

        $influencer->update(['physical_desc' => InfluencerPromptBuilder::physicalDesc($influencer->promptData())]);

        GenerateInfluencerSetJob::dispatch(
            $influencer,
            $validated['provider'],
            $validated['model'],
            $validated['aspect_ratio'],
            enhance: $request->boolean('enhance'),
        );

        return redirect()->route('influencers.show', $influencer);
    }

    public function show(Request $request, Influencer $influencer): View
    {
        $this->authorizeTeam($influencer);

        return view('influencers.show', [
            'influencer' => $influencer,
            'providers' => $this->mediaProviders($this->team($request)),
        ]);
    }

    public function status(Request $request, Influencer $influencer): JsonResponse
    {
        $this->authorizeTeam($influencer);

        return response()->json([
            'status' => $influencer->status,
            'sets' => $influencer->generation_history ?? [],
        ]);
    }

    public function generateSet(Request $request, Influencer $influencer): RedirectResponse
    {
        $this->authorizeTeam($influencer);

        $validated = $request->validate([
            'provider' => ['required', 'string'],
            'model' => ['required', 'string', 'max:255'],
            'aspect_ratio' => ['required', 'in:9:16,16:9'],
        ]);

        GenerateInfluencerSetJob::dispatch($influencer, $validated['provider'], $validated['model'], $validated['aspect_ratio']);

        return back();
    }

    public function choose(Request $request, Influencer $influencer): RedirectResponse
    {
        $this->authorizeTeam($influencer);

        $request->validate(['image_id' => ['required', 'string']]);

        foreach (($influencer->generation_history ?? []) as $set) {
            foreach ($set['images'] ?? [] as $image) {
                if (($image['id'] ?? null) === $request->image_id) {
                    $influencer->update([
                        'main_image' => $image['url'],
                        'prompt' => $image['prompt'] ?? null,
                    ]);

                    return redirect()->route('influencers.show', $influencer)->with('success', __('influencers.chosen'));
                }
            }
        }

        return back()->withErrors(['image_id' => __('influencers.image_not_found')]);
    }

    private function team(Request $request): Team
    {
        return $request->user()->currentTeam();
    }

    private function authorizeTeam(Influencer $influencer): void
    {
        abort_unless($influencer->team_id === $this->team(request())?->getKey(), 404);
    }

    /**
     * The media providers the team has connected, with their model catalogs.
     */
    private function mediaProviders(Team $team): Collection
    {
        return ApiProvider::query()
            ->where('type', ApiProviderType::Media)
            ->get()
            ->filter(fn (ApiProvider $provider) => $team->connectionFor($provider) !== null)
            ->map(fn (ApiProvider $provider) => [
                'provider' => $provider,
                'models' => config('services.'.$provider->machine_name.'.models', []),
            ])
            ->filter(fn (array $item) => $item['models'] !== []);
    }
}
