<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\HalalCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * List all companies — owner only.
     */
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

    /**
     * Enter a company context — sets the session.
     */
    public function enter(Request $request, Company $company)
    {
        abort_if($request->user()->role !== 'owner', 403);

        session(['active_company_id' => $company->id]);

        return redirect()->route('dashboard')
            ->with('success', "Masuk ke {$company->name}");
    }

    /**
     * Leave company context — clears session.
     */
    public function leave(Request $request)
    {
        abort_if($request->user()->role !== 'owner', 403);

        session()->forget('active_company_id');

        return redirect()->route('companies.index');
    }
}