<?php

namespace App\Services;

use App\DTOs\IngredientDTO;
use App\Models\AuditLog;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class IngredientService
{
    /**
     * Get all root-level ingredients for a company, with children eager-loaded.
     */
    public function getTree(int $companyId): Collection
    {
        return Ingredient::where('company_id', $companyId)
            ->rootLevel()
            ->with(['childrenRecursive.halalCertificates', 'supplier', 'halalCertificates'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a single ingredient with its full tree + certificates.
     */
    public function getById(int $id, int $companyId): ?Ingredient
    {
        return Ingredient::where('company_id', $companyId)
            ->where('id', $id)
            ->with(['childrenRecursive.halalCertificates', 'supplier', 'halalCertificates', 'parent'])
            ->first();
    }

    /**
     * Create an ingredient (and its children recursively if composite).
     */
    public function create(IngredientDTO $dto): Ingredient
    {
        return DB::transaction(function () use ($dto) {
            $ingredient = Ingredient::create($dto->toArray());

            if ($dto->type === 'composite' && !empty($dto->children)) {
                $this->createChildren($ingredient, $dto->children);
            }

            AuditLog::log($ingredient, 'created', null, $ingredient->toArray());

            return $ingredient->load('childrenRecursive');
        });
    }

    /**
     * Update an existing ingredient.
     */
    public function update(Ingredient $ingredient, IngredientDTO $dto): Ingredient
    {
        return DB::transaction(function () use ($ingredient, $dto) {
            $oldValues = $ingredient->toArray();
            $ingredient->update($dto->toArray());

            AuditLog::log($ingredient, 'updated', $oldValues, $ingredient->fresh()->toArray());

            return $ingredient->fresh(['childrenRecursive', 'supplier', 'halalCertificates']);
        });
    }

    /**
     * Delete an ingredient and its children.
     */
    public function delete(Ingredient $ingredient): bool
    {
        return DB::transaction(function () use ($ingredient) {
            AuditLog::log($ingredient, 'deleted', $ingredient->toArray());

            // Children are soft-deleted via cascade or manually
            $this->deleteChildrenRecursive($ingredient);

            return $ingredient->delete();
        });
    }

    /**
     * Add a child ingredient to a composite parent.
     */
    public function addChild(Ingredient $parent, IngredientDTO $childDto): Ingredient
    {
        if ($parent->type !== 'composite') {
            throw new \InvalidArgumentException("Cannot add children to a simple ingredient.");
        }

        $childData = $childDto->toArray();
        $childData['parent_id'] = $parent->id;
        $childData['company_id'] = $parent->company_id;

        return DB::transaction(function () use ($parent, $childData) {
            $child = Ingredient::create($childData);

            AuditLog::log($child, 'created', null, $child->toArray());

            return $child;
        });
    }

    /**
     * Collect all leaf-level ingredient IDs for a given ingredient.
     * If simple → returns itself. If composite → returns all deepest children.
     */
    public function getLeafIngredientIds(Ingredient $ingredient): array
    {
        if ($ingredient->type === 'simple') {
            return [$ingredient->id];
        }

        $leafIds = [];
        $children = $ingredient->children()->with('childrenRecursive')->get();

        foreach ($children as $child) {
            $leafIds = array_merge($leafIds, $this->getLeafIngredientIds($child));
        }

        return $leafIds;
    }

    /**
     * Get ingredients that have no valid certificate.
     */
    public function getUncertifiedIngredients(int $companyId): Collection
    {
        return Ingredient::where('company_id', $companyId)
            ->where('type', 'simple')
            ->where(function ($query) {
                $query->doesntHave('halalCertificates')
                    ->orWhereHas('halalCertificates', function ($q) {
                        $q->where('status', '!=', 'valid')
                            ->where('status', '!=', 'expiring_soon');
                    });
            })
            ->with('supplier')
            ->get();
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    /**
     * Recursively create children from an array of IngredientDTOs.
     *
     * @param IngredientDTO[] $children
     */
    private function createChildren(Ingredient $parent, array $children): void
    {
        foreach ($children as $childDto) {
            $childData = $childDto->toArray();
            $childData['parent_id'] = $parent->id;
            $childData['company_id'] = $parent->company_id;

            $child = Ingredient::create($childData);

            if ($childDto->type === 'composite' && !empty($childDto->children)) {
                $this->createChildren($child, $childDto->children);
            }
        }
    }

    private function deleteChildrenRecursive(Ingredient $ingredient): void
    {
        foreach ($ingredient->children as $child) {
            $this->deleteChildrenRecursive($child);
            $child->delete();
        }
    }
}