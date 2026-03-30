<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private HalalHealthScoreService $scoreService;

    public function __construct(HalalHealthScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    /**
     * Get all products for a company with their ingredients and halal status.
     */
    public function getAll(int $companyId, array $filters = []): Collection
    {
        $query = Product::where('company_id', $companyId)
            ->with(['facility', 'ingredients.halalCertificates']);

        if (isset($filters['facility_id'])) {
            $query->where('facility_id', $filters['facility_id']);
        }

        if (isset($filters['halal_status'])) {
            $query->where('halal_status', $filters['halal_status']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get a single product with full details.
     */
    public function getById(int $id, int $companyId): ?Product
    {
        return Product::where('company_id', $companyId)
            ->where('id', $id)
            ->with([
                'facility',
                'ingredients.halalCertificates',
                'ingredients.supplier',
                'ingredients.childrenRecursive.halalCertificates',
            ])
            ->first();
    }

    /**
     * Create a product and sync its ingredients.
     */
    public function create(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $product = Product::create($dto->toArray());

            if (!empty($dto->ingredients)) {
                $this->syncIngredients($product, $dto->ingredients);
            }

            // Calculate initial halal score
            $this->scoreService->calculateAndSave($product);

            AuditLog::log($product, 'created', null, $product->toArray());

            return $product->fresh(['facility', 'ingredients']);
        });
    }

    /**
     * Update a product and re-sync ingredients if provided.
     */
    public function update(Product $product, ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($product, $dto) {
            $oldValues = $product->toArray();

            $product->update($dto->toArray());

            if (!empty($dto->ingredients)) {
                $this->syncIngredients($product, $dto->ingredients);
            }

            // Recalculate halal score after update
            $this->scoreService->calculateAndSave($product);

            AuditLog::log($product, 'updated', $oldValues, $product->fresh()->toArray());

            return $product->fresh(['facility', 'ingredients']);
        });
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            AuditLog::log($product, 'deleted', $product->toArray());

            $product->ingredients()->detach();

            return $product->delete();
        });
    }

    /**
     * Add a single ingredient to a product.
     */
    public function addIngredient(
        Product $product,
        int $ingredientId,
        array $pivotData = []
    ): Product {
        return DB::transaction(function () use ($product, $ingredientId, $pivotData) {
            $product->ingredients()->attach($ingredientId, $pivotData);

            $this->scoreService->calculateAndSave($product);

            return $product->fresh('ingredients');
        });
    }

    /**
     * Remove an ingredient from a product.
     */
    public function removeIngredient(Product $product, int $ingredientId): Product
    {
        return DB::transaction(function () use ($product, $ingredientId) {
            $product->ingredients()->detach($ingredientId);

            $this->scoreService->calculateAndSave($product);

            return $product->fresh('ingredients');
        });
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    /**
     * Sync ingredients from ProductIngredientDTO array.
     *
     * @param \App\DTOs\ProductIngredientDTO[] $ingredientDtos
     */
    private function syncIngredients(Product $product, array $ingredientDtos): void
    {
        $syncData = [];

        foreach ($ingredientDtos as $dto) {
            $syncData[$dto->ingredient_id] = $dto->toPivotArray();
        }

        $product->ingredients()->sync($syncData);
    }
}