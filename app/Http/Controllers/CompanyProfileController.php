<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'signatureUrl' => $company->signature_path
                ? asset('storage/' . $company->signature_path)
                : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $companyId = $user->activeCompanyId();
        $company = Company::findOrFail($companyId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npwp' => 'required|string|max:30',
            'bpjph_registration_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Handle signature upload
        if ($request->hasFile('signature')) {
            // Delete old signature
            if ($company->signature_path) {
                Storage::disk('public')->delete($company->signature_path);
            }

            $path = $request->file('signature')->store('signatures', 'public');
            $validated['signature_path'] = $path;
        }

        unset($validated['signature']);
        $company->update($validated);

        return back()->with('success', __('Company profile updated.'));
    }

    public function deleteSignature(Request $request)
    {
        $companyId = $request->user()->activeCompanyId();
        $company = Company::findOrFail($companyId);

        if ($company->signature_path) {
            Storage::disk('public')->delete($company->signature_path);
            $company->update(['signature_path' => null]);
        }

        return back()->with('success', __('Signature removed.'));
    }
}