<?php

namespace App\Http\Controllers;

use App\DTOs\FacilityDTO;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\AuditLog;
use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function index(Request $request): Response
    {
        $facilities = Facility::where('company_id', $request->user()->activeCompanyId())
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return Inertia::render('Facilities/Index', [
            'facilities' => $facilities,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Facilities/Create');
    }

    public function store(StoreFacilityRequest $request)
    {
        $dto = FacilityDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $facility = Facility::create($dto->toArray());

        AuditLog::log($facility, 'created', null, $facility->toArray());

        return redirect()->route('facilities.index')
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function show(Request $request, Facility $facility): Response
    {
        abort_if($facility->company_id !== $request->user()->activeCompanyId(), 403);

        $facility->load(['products' => fn ($q) => $q->active(), 'sjphDocuments']);

        return Inertia::render('Facilities/Show', [
            'facility' => $facility,
        ]);
    }

    public function edit(Request $request, Facility $facility): Response
    {
        abort_if($facility->company_id !== $request->user()->activeCompanyId(), 403);

        return Inertia::render('Facilities/Edit', [
            'facility' => $facility,
        ]);
    }

    public function update(StoreFacilityRequest $request, Facility $facility)
    {
        abort_if($facility->company_id !== $request->user()->activeCompanyId(), 403);

        $oldValues = $facility->toArray();
        $dto = FacilityDTO::fromRequest($request->validated(), $request->user()->activeCompanyId());

        $facility->update($dto->toArray());

        AuditLog::log($facility, 'updated', $oldValues, $facility->fresh()->toArray());

        return redirect()->route('facilities.show', $facility)
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Request $request, Facility $facility)
    {
        abort_if($facility->company_id !== $request->user()->activeCompanyId(), 403);

        AuditLog::log($facility, 'deleted', $facility->toArray());
        $facility->delete();

        return redirect()->route('facilities.index')
            ->with('success', 'Fasilitas berhasil dihapus.');
    }
}