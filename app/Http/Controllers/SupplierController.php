<?php

namespace App\Http\Controllers;

use App\DTOs\SupplierDTO;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\AuditLog;
use App\Models\Supplier;
use App\Models\SupplierAccessToken;
use App\Services\SupplierPortalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $suppliers = Supplier::where('company_id', $request->user()->activeCompanyId())
            ->withCount('ingredients')
            ->orderBy('name')
            ->get();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Suppliers/Create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $dto = SupplierDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $supplier = Supplier::create($dto->toArray());

        AuditLog::log($supplier, 'created', null, $supplier->toArray());

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function show(Request $request, Supplier $supplier): Response
    {
        abort_if($supplier->company_id !== $request->user()->activeCompanyId(), 403);

        $supplier->load(['ingredients.halalCertificates', 'accessTokens' => fn ($q) => $q->valid()]);

        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier,
        ]);
    }

    public function edit(Request $request, Supplier $supplier): Response
    {
        abort_if($supplier->company_id !== $request->user()->activeCompanyId(), 403);

        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        abort_if($supplier->company_id !== $request->user()->activeCompanyId(), 403);

        $oldValues = $supplier->toArray();
        $dto = SupplierDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $supplier->update($dto->toArray());

        AuditLog::log($supplier, 'updated', $oldValues, $supplier->fresh()->toArray());

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        abort_if($supplier->company_id !== $request->user()->activeCompanyId(), 403);

        AuditLog::log($supplier, 'deleted', $supplier->toArray());
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus.');
    }

    public function generateToken(Request $request, Supplier $supplier, SupplierPortalService $portalService)
    {
        abort_if($supplier->company_id !== $request->user()->activeCompanyId(), 403);

        $request->validate([
            'ingredient_id' => 'nullable|exists:ingredients,id',
            'valid_days' => 'nullable|integer|min:1|max:90',
        ]);

        $token = $portalService->generateAccessToken(
            $request->user()->activeCompanyId(),
            $supplier->id,
            $request->input('ingredient_id'),
            $request->input('valid_days', 30)
        );

        $portalUrl = route('supplier-portal.show', $token->token);

        return back()->with('success', "Portal link berhasil dibuat: {$portalUrl}");
    }

    public function revokeToken(Request $request, SupplierAccessToken $token, SupplierPortalService $portalService)
    {
        abort_if($token->company_id !== $request->user()->activeCompanyId(), 403);

        $portalService->revokeToken($token);

        return back()->with('success', 'Token akses berhasil dicabut.');
    }
}