<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();
        $company = Company::findOrFail($companyId);

        return Inertia::render('CompanyProfile/Show', [
            'company' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $companyId = $user->activeCompanyId();

        // Only admin/owner can edit
        abort_if(!$user->is_admin, 403);

        $company = Company::findOrFail($companyId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:20',
            'bpjph_registration_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
        ]);

        $oldValues = $company->toArray();
        $company->update($validated);

        AuditLog::log($company, 'updated', $oldValues, $company->fresh()->toArray());

        return back()->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}