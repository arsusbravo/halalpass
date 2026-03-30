<?php

namespace App\Http\Controllers;

use App\DTOs\IngredientDTO;
use App\Http\Requests\StoreIngredientRequest;
use App\Models\Ingredient;
use App\Models\Supplier;
use App\Services\IngredientService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IngredientController extends Controller
{
    private IngredientService $ingredientService;

    public function __construct(IngredientService $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $ingredients = $this->ingredientService->getTree($companyId);

        return Inertia::render('Ingredients/Index', [
            'ingredients' => $ingredients,
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $suppliers = Supplier::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Ingredients/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(StoreIngredientRequest $request)
    {
        $dto = IngredientDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $ingredient = $this->ingredientService->create($dto);

        return redirect()->route('ingredients.show', $ingredient)
            ->with('success', 'Bahan berhasil ditambahkan.');
    }

    public function show(Request $request, Ingredient $ingredient): Response
    {
        abort_if($ingredient->company_id !== $request->user()->activeCompanyId(), 403);

        $ingredient = $this->ingredientService->getById($ingredient->id, $request->user()->activeCompanyId());

        return Inertia::render('Ingredients/Show', [
            'ingredient' => $ingredient,
        ]);
    }

    public function edit(Request $request, Ingredient $ingredient): Response
    {
        abort_if($ingredient->company_id !== $request->user()->activeCompanyId(), 403);

        $companyId = $request->user()->activeCompanyId();

        $suppliers = Supplier::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $ingredient->load('childrenRecursive');

        return Inertia::render('Ingredients/Edit', [
            'ingredient' => $ingredient,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(StoreIngredientRequest $request, Ingredient $ingredient)
    {
        abort_if($ingredient->company_id !== $request->user()->activeCompanyId(), 403);

        $dto = IngredientDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $this->ingredientService->update($ingredient, $dto);

        return redirect()->route('ingredients.show', $ingredient)
            ->with('success', 'Bahan berhasil diperbarui.');
    }

    public function destroy(Request $request, Ingredient $ingredient)
    {
        abort_if($ingredient->company_id !== $request->user()->activeCompanyId(), 403);

        $this->ingredientService->delete($ingredient);

        return redirect()->route('ingredients.index')
            ->with('success', 'Bahan berhasil dihapus.');
    }

    public function addChild(StoreIngredientRequest $request, Ingredient $ingredient)
    {
        abort_if($ingredient->company_id !== $request->user()->activeCompanyId(), 403);

        $dto = IngredientDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $this->ingredientService->addChild($ingredient, $dto);

        return redirect()->route('ingredients.show', $ingredient)
            ->with('success', 'Sub-bahan berhasil ditambahkan.');
    }
}