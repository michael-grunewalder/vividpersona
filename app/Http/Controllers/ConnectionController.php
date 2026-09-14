<?php

namespace App\Http\Controllers;

use App\Models\ApiProvider;
use App\Models\ProviderConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConnectionController extends Controller
{
    /**
     * List the available API providers and the current team's connections.
     */
    public function index(): View
    {
        $team = auth()->user()->currentTeam();

        $providers = ApiProvider::query()
            ->orderBy('type')
            ->orderBy('friendly_name')
            ->get()
            ->map(fn (ApiProvider $provider) => [
                'provider' => $provider,
                'connection' => $provider->connectionFor($team),
            ]);

        return view('connections.index', ['providers' => $providers]);
    }

    /**
     * Store the current team's credentials for a provider.
     */
    public function store(Request $request, ApiProvider $provider): RedirectResponse
    {
        $team = $request->user()->currentTeam();

        $rules = [];

        foreach ($provider->meta ?? [] as $field) {
            $rules['credentials.'.$field['name']] = ['required', 'string', 'max:2048'];
        }

        $validated = $request->validate($rules);

        $credentials = collect($provider->meta ?? [])
            ->mapWithKeys(fn (array $field) => [$field['name'] => $validated['credentials'][$field['name']]])
            ->all();

        ProviderConnection::updateOrCreate(
            ['team_id' => $team->getKey(), 'provider_id' => $provider->getKey()],
            ['credentials' => $credentials],
        );

        return redirect()->route('connections.index')->with('success', __('connections.connected_msg'));
    }
}
