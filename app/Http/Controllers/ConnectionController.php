<?php

namespace App\Http\Controllers;

use App\Enums\ApiService;
use App\Enums\ApiServiceType;
use App\Models\TeamApiCredential;
use App\Services\Connections\ApiCredentialVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConnectionController extends Controller
{
    /**
     * List the API services grouped by type with the current team's connections.
     */
    public function index(): View
    {
        $team = auth()->user()->currentTeam();

        $groups = collect(ApiService::cases())
            ->map(fn (ApiService $service) => [
                'service' => $service,
                'connected' => $team->hasCredential($service),
            ])
            ->groupBy(fn (array $item) => $item['service']->type()->value);

        return view('connections.index', [
            'media' => $groups->get(ApiServiceType::Media->value, collect()),
            'llm' => $groups->get(ApiServiceType::Llm->value, collect()),
            'defaultLlm' => $team->defaultLlmService(),
        ]);
    }

    /**
     * Store the current team's credentials for a service.
     */
    public function store(Request $request, ApiService $service): RedirectResponse
    {
        $team = $request->user()->currentTeam();

        $rules = [];

        foreach ($service->credentialFields() as $field) {
            $rules[$service->value.'.'.$field] = ['required', 'string', 'max:2048'];
        }

        $validated = $request->validate($rules);

        $apiKey = (string) $validated[$service->value]['api_key'];

        if (! app(ApiCredentialVerifier::class)->verify($service, $apiKey)) {
            return back()->withErrors([$service->value.'.api_key' => __('connections.invalid_key')])->withInput();
        }

        $credentials = array_merge(
            $team->credentialFor($service)?->credentials ?? [],
            collect($service->credentialFields())
                ->mapWithKeys(fn (string $field) => [$field => $validated[$service->value][$field]])
                ->all(),
        );

        TeamApiCredential::updateOrCreate(
            ['team_id' => $team->getKey(), 'service' => $service->value],
            ['credentials' => $credentials],
        );

        return redirect()->route('connections.index')
            ->with('success', __('connections.connected_msg', ['service' => $service->label()]));
    }

    /**
     * Remove the current team's credentials for a service.
     */
    public function disconnect(Request $request, ApiService $service): RedirectResponse
    {
        $request->user()->currentTeam()
            ->apiCredentials()
            ->where('service', $service->value)
            ->delete();

        return redirect()->route('connections.index')
            ->with('success', __('connections.disconnected_msg', ['service' => $service->label()]));
    }

    /**
     * Set the team's default LLM service.
     */
    public function updateDefaultLlm(Request $request): RedirectResponse
    {
        $team = $request->user()->currentTeam();

        $validated = $request->validate([
            'default_llm' => ['nullable', Rule::in(ApiService::llmValues())],
        ]);

        $team->update(['default_llm' => $validated['default_llm'] ?? null]);

        return redirect()->route('connections.index')->with('success', __('connections.default_llm_saved'));
    }
}
