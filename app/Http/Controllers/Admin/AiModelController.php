<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AiModelType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiModelRequest;
use App\Http\Requests\UpdateAiModelRequest;
use App\Models\AiModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiModelController extends Controller
{
    /**
     * List the curated AI models.
     */
    public function index(): View
    {
        $models = AiModel::query()
            ->ordered()
            ->withCount('personas')
            ->paginate(20);

        return view('admin.ai-models.index', [
            'models' => $models,
            'types' => AiModelType::cases(),
        ]);
    }

    /**
     * Show the form to create a new model.
     */
    public function create(): View
    {
        return view('admin.ai-models.create');
    }

    /**
     * Store a newly created model.
     */
    public function store(StoreAiModelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['enabled'] = $request->boolean('enabled');

        AiModel::query()->create($data);

        return redirect()->route('backend.ai-model.index')->with('success', __('admin.ai_models.created'));
    }

    /**
     * Show the form to edit an existing model.
     */
    public function edit(AiModel $aiModel): View
    {
        return view('admin.ai-models.edit', ['model' => $aiModel]);
    }

    /**
     * Update an existing model.
     */
    public function update(UpdateAiModelRequest $request, AiModel $aiModel): RedirectResponse
    {
        $data = $request->validated();
        $data['enabled'] = $request->boolean('enabled');

        $aiModel->update($data);

        return redirect()->route('backend.ai-model.index')->with('success', __('admin.ai_models.updated'));
    }

    /**
     * Delete an existing model.
     */
    public function destroy(AiModel $aiModel): RedirectResponse
    {
        $aiModel->delete();

        return redirect()->route('backend.ai-model.index')->with('success', __('admin.ai_models.deleted'));
    }
}
