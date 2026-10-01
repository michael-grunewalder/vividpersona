<?php

namespace App\Services\Media;

use App\Enums\AiModelType;
use App\Enums\ApiService;
use App\Models\AiModel;
use Illuminate\Support\Collection;

/**
 * Reads the admin-curated `ai_models` catalog for a given type and provider and
 * exposes it as a family → version → variant selection tree.
 */
final class AiModelCatalog
{
    /**
     * The enabled models of a type for a provider, ordered for display.
     *
     * @return Collection<int, AiModel>
     */
    public function models(AiModelType $type, ApiService $provider): Collection
    {
        return AiModel::query()
            ->enabled()
            ->ofType($type)
            ->forProvider($provider)
            ->ordered()
            ->get();
    }

    /**
     * The enabled models grouped into a family → version → variant tree. Each
     * variant leaf carries the selectable ai_model id. A level with a single
     * option is auto-selected by the UI and its select hidden.
     *
     * @return array<int, array{key: string, label: string, versions: array<int, array{key: string, label: string, variants: array<int, array{key: string, label: string, id: string}>}>}>
     */
    public function tree(AiModelType $type, ApiService $provider): array
    {
        $families = [];

        foreach ($this->models($type, $provider) as $model) {
            $familyKey = $model->family;
            $versionKey = (string) $model->version;
            $variantKey = (string) $model->variant;

            $families[$familyKey] ??= ['key' => $familyKey, 'label' => $model->name, 'versions' => []];
            $versions = &$families[$familyKey]['versions'];

            $versions[$versionKey] ??= ['key' => $versionKey, 'label' => $model->version ?? $model->name, 'variants' => []];
            $variants = &$versions[$versionKey]['variants'];

            $variants[$variantKey] = [
                'key' => $variantKey,
                'label' => $model->variant ?? $model->label(),
                'id' => $model->getKey(),
            ];
        }

        foreach ($families as $familyKey => $family) {
            uksort($families[$familyKey]['versions'], fn (string $a, string $b) => version_compare($a, $b) ?: strcmp($a, $b));

            foreach (array_keys($families[$familyKey]['versions']) as $versionKey) {
                uksort($families[$familyKey]['versions'][$versionKey]['variants'], 'strcmp');
            }
        }

        return array_values(array_map(
            fn (array $family): array => [
                'key' => $family['key'],
                'label' => $family['label'],
                'versions' => array_values(array_map(
                    fn (array $version): array => [
                        'key' => $version['key'],
                        'label' => $version['label'],
                        'variants' => array_values($version['variants']),
                    ],
                    $family['versions'],
                )),
            ],
            $families,
        ));
    }

    /**
     * Resolve an enabled model by id, if any.
     */
    public function find(string $id): ?AiModel
    {
        return AiModel::query()->enabled()->find($id);
    }

    /**
     * The first enabled model of a type for a provider, used by the default
     * generation mode when no explicit model is chosen.
     */
    public function defaultFor(AiModelType $type, ApiService $provider): ?AiModel
    {
        return $this->models($type, $provider)->first();
    }
}
