<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\HalalCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        abort_if($request->user()->role !== 'owner', 403);

        $companies = Company::withCount(['facilities', 'products', 'users', 'ingredients'])
            ->orderBy('name')
            ->get()
            ->map(function ($company) {
                $certs = HalalCertificate::where('company_id', $company->id)->get();
                $company->cert_summary = [
                    'valid' => $certs->where('status', 'valid')->count(),
                    'expiring_soon' => $certs->where('status', 'expiring_soon')->count(),
                    'expired' => $certs->where('status', 'expired')->count(),
                ];
                return $company;
            });

        $activeCompanyId = session('active_company_id');

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'activeCompanyId' => $activeCompanyId,
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()->role !== 'owner', 403);

        return Inertia::render('Companies/Create');
    }

    public function store(Request $request)
    {
        abort_if($request->user()->role !== 'owner', 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:20',
        ]);

        $validated['status'] = 'active';

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', __('Company created successfully.'));
    }

    public function toggleStatus(Request $request, Company $company)
    {
        abort_if($request->user()->role !== 'owner', 403);

        $newStatus = $company->status === 'active' ? 'inactive' : 'active';
        $company->update(['status' => $newStatus]);

        $message = $newStatus === 'active'
            ? __('Company activated successfully.')
            : __('Company deactivated successfully.');

        return redirect()->route('companies.index')->with('success', $message);
    }

    public function destroy(Request $request, Company $company)
    {
        abort_if($request->user()->role !== 'owner', 403);

        // Prevent deleting companies that have users
        if ($company->users()->count() > 0) {
            return redirect()->route('companies.index')
                ->with('error', __('Cannot delete a company that still has users. Remove all users first.'));
        }

        $company->delete();

        // Clear session if the deleted company was active
        if (session('active_company_id') === $company->id) {
            session()->forget('active_company_id');
        }

        return redirect()->route('companies.index')
            ->with('success', __('Company deleted successfully.'));
    }

    public function enter(Request $request, Company $company)
    {
        abort_if($request->user()->role !== 'owner', 403);

        session(['active_company_id' => $company->id]);

        return redirect()->route('dashboard')
            ->with('success', __('Entered :name', ['name' => $company->name]));
    }

    public function leave(Request $request)
    {
        abort_if($request->user()->role !== 'owner', 403);

        session()->forget('active_company_id');

        return redirect()->route('companies.index');
    }
}