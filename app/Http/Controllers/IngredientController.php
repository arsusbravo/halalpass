<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IngredientController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $ingredients = Ingredient::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return Inertia::render('Ingredients/Index', [
            'ingredients' => $ingredients,
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $parentIngredients = Ingredient::where('company_id', $companyId)
            ->where('type', 'composite')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ingredients/Create', [
            'suppliers' => $suppliers,
            'parentIngredients' => $parentIngredients,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->activeCompanyId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type' => 'nullable|in:simple,composite',
            'parent_id' => 'nullable|exists:ingredients,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'nullable|in:bahan_baku,bahan_tambahan,bahan_penolong',
            'halal_risk_level' => 'nullable|in:no_risk,low_risk,medium_risk,high_risk',
            'sh_number' => 'nullable|string|max:100',
            // Optional advanced fields
            'origin_country' => 'nullable|string|max:2',
            'manufacturer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['code'] = Ingredient::generateCode('BHN', $companyId);
        $validated['type'] = $validated['type'] ?? 'simple';
        $validated['category'] = $validated['category'] ?? 'bahan_baku';
        $validated['halal_risk_level'] = $validated['halal_risk_level'] ?? 'medium_risk';

        // Auto-create supplier from brand if no supplier selected and brand is provided
        if (empty($validated['supplier_id']) && !empty($validated['brand'])) {
            $supplier = Supplier::firstOrCreate(
                ['company_id' => $companyId, 'name' => $validated['brand']],
                [
                    'code' => Supplier::generateCode('SUP', $companyId),
                    'country' => $validated['origin_country'] ?? 'ID',
                    'status' => 'active',
                ]
            );
            $validated['supplier_id'] = $supplier->id;
        }

        Ingredient::create($validated);

        return redirect()->route('ingredients.index')
            ->with('success', __('Ingredient created successfully.'));
    }

    public function show(Request $request, Ingredient $ingredient): Response
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($ingredient->company_id !== $companyId, 403);

        $ingredient->load(['children', 'supplier', 'halalCertificates']);

        return Inertia::render('Ingredients/Show', [
            'ingredient' => $ingredient,
        ]);
    }

    public function edit(Request $request, Ingredient $ingredient): Response
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($ingredient->company_id !== $companyId, 403);

        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $parentIngredients = Ingredient::where('company_id', $companyId)
            ->where('type', 'composite')
            ->where('id', '!=', $ingredient->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Ingredients/Edit', [
            'ingredient' => $ingredient,
            'suppliers' => $suppliers,
            'parentIngredients' => $parentIngredients,
        ]);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($ingredient->company_id !== $companyId, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type' => 'nullable|in:simple,composite',
            'parent_id' => 'nullable|exists:ingredients,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'nullable|in:bahan_baku,bahan_tambahan,bahan_penolong',
            'halal_risk_level' => 'nullable|in:no_risk,low_risk,medium_risk,high_risk',
            'sh_number' => 'nullable|string|max:100',
            'origin_country' => 'nullable|string|max:2',
            'manufacturer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $ingredient->update($validated);

        return redirect()->route('ingredients.index')
            ->with('success', __('Ingredient updated successfully.'));
    }

    public function destroy(Request $request, Ingredient $ingredient)
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($ingredient->company_id !== $companyId, 403);

        $ingredient->delete();

        return redirect()->route('ingredients.index')
            ->with('success', __('Ingredient deleted successfully.'));
    }

    public function bulkCreate(): \Inertia\Response
    {
        return Inertia::render('Ingredients/BulkCreate');
    }
    
    public function bulkStore(Request $request)
    {
        $companyId = $request->user()->activeCompanyId();
    
        $validated = $request->validate([
            'names' => 'required|string',
            'halal_risk_level' => 'nullable|in:no_risk,low_risk,medium_risk,high_risk',
        ]);
    
        $riskLevel = $validated['halal_risk_level'] ?? 'medium_risk';
    
        $names = collect(explode("\n", $validated['names']))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->unique()
            ->values();
    
        if ($names->isEmpty()) {
            return back()->with('error', __('No ingredient names provided.'));
        }
    
        $created = 0;
    
        foreach ($names as $name) {
            $exists = Ingredient::where('company_id', $companyId)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            // Check if another company already has this ingredient with a certificate
            $existing = Ingredient::where('name', $name)
                ->whereNotNull('sh_number')
                ->where('sh_number', '!=', '')
                ->first();

            Ingredient::create([
                'company_id' => $companyId,
                'name' => $name,
                'code' => Ingredient::generateCode('BHN', $companyId),
                'type' => 'simple',
                'category' => $existing->category ?? 'bahan_baku',
                'halal_risk_level' => $existing->halal_risk_level ?? 'medium_risk',
                'brand' => $existing->brand ?? null,
                'sh_number' => $existing->sh_number ?? null,
            ]);

            $created++;
        }
    
        $skipped = $names->count() - $created;
        $message = __(':count ingredients created successfully.', ['count' => $created]);
    
        if ($skipped > 0) {
            $message .= ' ' . __(':count skipped (already exist).', ['count' => $skipped]);
        }
    
        return redirect()->route('ingredients.index')->with('success', $message);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');
    
        if (strlen($query) < 2) {
            return response()->json([]);
        }
    
        $results = Ingredient::whereNotNull('sh_number')
            ->where('sh_number', '!=', '')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('brand', 'like', "%{$query}%");
            })
            ->select('name', 'brand', 'sh_number', 'halal_risk_level', 'category')
            ->groupBy('name', 'brand', 'sh_number', 'halal_risk_level', 'category')
            ->limit(10)
            ->get();
    
        return response()->json($results);
    }
}