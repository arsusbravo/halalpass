<?php

namespace App\Http\Controllers;

use App\DTOs\ProductDTO;
use App\Http\Requests\StoreProductRequest;
use App\Models\Facility;
use App\Models\Ingredient;
use App\Models\Product;
use App\Services\HalalHealthScoreService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    private ProductService $productService;
    private HalalHealthScoreService $scoreService;

    public function __construct(ProductService $productService, HalalHealthScoreService $scoreService)
    {
        $this->productService = $productService;
        $this->scoreService = $scoreService;
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $products = $this->productService->getAll($companyId, $request->only([
            'facility_id', 'halal_status', 'status',
        ]));

        $summary = $this->scoreService->getCompanySummary($companyId);

        $facilities = Facility::where('company_id', $companyId)
            ->active()
            ->get(['id', 'name', 'code']);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'summary' => $summary,
            'facilities' => $facilities,
            'filters' => $request->only(['facility_id', 'halal_status', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $facilities = Facility::where('company_id', $companyId)
            ->active()
            ->get(['id', 'name', 'code']);

        $ingredients = Ingredient::where('company_id', $companyId)
            ->rootLevel()
            ->with('halalCertificates')
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Create', [
            'facilities' => $facilities,
            'ingredients' => $ingredients,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $product = $this->productService->create($dto);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Request $request, Product $product): Response
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $product = $this->productService->getById($product->id, $request->user()->activeCompanyId());

        $scoreResult = $this->scoreService->calculateForProduct($product);

        return Inertia::render('Products/Show', [
            'product' => $product,
            'scoreResult' => $scoreResult->toArray(),
        ]);
    }

    public function edit(Request $request, Product $product): Response
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $companyId = $request->user()->activeCompanyId();

        $product->load('ingredients');

        $facilities = Facility::where('company_id', $companyId)
            ->active()
            ->get(['id', 'name', 'code']);

        $ingredients = Ingredient::where('company_id', $companyId)
            ->rootLevel()
            ->with('halalCertificates')
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'facilities' => $facilities,
            'ingredients' => $ingredients,
        ]);
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $dto = ProductDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $this->productService->update($product, $dto);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $this->productService->delete($product);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function addIngredient(Request $request, Product $product)
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'is_critical' => 'nullable|boolean',
            'usage_purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->productService->addIngredient($product, $request->ingredient_id, [
            'percentage' => $request->percentage,
            'is_critical' => $request->boolean('is_critical'),
            'usage_purpose' => $request->usage_purpose,
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return back()->with('success', 'Bahan berhasil ditambahkan ke produk.');
    }

    public function removeIngredient(Request $request, Product $product, Ingredient $ingredient)
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $this->productService->removeIngredient($product, $ingredient->id);

        return back()->with('success', 'Bahan berhasil dihapus dari produk.');
    }

    public function recalculate(Request $request, Product $product)
    {
        abort_if($product->company_id !== $request->user()->activeCompanyId(), 403);

        $result = $this->scoreService->calculateAndSave($product);

        return back()->with('success', "Skor halal diperbarui: {$result->score}/100 ({$result->status})");
    }
}