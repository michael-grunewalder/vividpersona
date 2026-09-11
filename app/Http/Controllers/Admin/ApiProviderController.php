<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveApiProviderRequest;
use App\Models\ApiProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApiProviderController extends Controller
{
    /**
     * List all API providers.
     */
    public function index(): View
    {
        $providers = ApiProvider::query()->latest()->get();

        return view('admin.providers.index', ['providers' => $providers]);
    }

    /**
     * Show the form to create a new provider.
     */
    public function create(): View
    {
        return view('admin.providers.create');
    }

    /**
     * Store a newly created provider.
     */
    public function store(SaveApiProviderRequest $request): RedirectResponse
    {
        ApiProvider::query()->create($request->validated());

        return redirect()->route('backend.api-provider.index')->with('success', __('admin.providers.created'));
    }

    /**
     * Show the form to edit an existing provider.
     */
    public function edit(ApiProvider $apiProvider): View
    {
        return view('admin.providers.edit', ['provider' => $apiProvider]);
    }

    /**
     * Update an existing provider.
     */
    public function update(SaveApiProviderRequest $request, ApiProvider $apiProvider): RedirectResponse
    {
        $apiProvider->update($request->validated());

        return redirect()->route('backend.api-provider.index')->with('success', __('admin.providers.updated'));
    }

    /**
     * Delete an existing provider.
     */
    public function destroy(ApiProvider $apiProvider): RedirectResponse
    {
        $apiProvider->delete();

        return redirect()->route('backend.api-provider.index')->with('success', __('admin.providers.deleted'));
    }
}
