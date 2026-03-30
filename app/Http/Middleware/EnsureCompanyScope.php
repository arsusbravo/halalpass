<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyScope
{
    /**
     * Ensure the user has a company context.
     *
     * - Owner: uses session('active_company_id'), set via Companies page.
     *   If not set, redirects to company selection page.
     * - Admin/Manager/Viewer: uses their own company_id from the users table.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Owner: platform-level, can switch companies
        if ($user->role === 'owner') {
            $activeCompanyId = session('active_company_id');

            if (!$activeCompanyId) {
                // Allow access to company selection page without scope
                if ($request->is('companies', 'companies/*', 'dashboard')) {
                    return $next($request);
                }

                return redirect()->route('companies.index')
                    ->with('error', 'Silakan pilih perusahaan terlebih dahulu.');
            }

            $request->merge(['_company_id' => $activeCompanyId]);
            return $next($request);
        }

        // Admin/Manager/Viewer: must have a fixed company
        if (!$user->company_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No company associated with your account.'], 403);
            }
            abort(403, 'No company associated with your account.');
        }

        $request->merge(['_company_id' => $user->company_id]);
        return $next($request);
    }

    /**
     * Get the active company ID for the current request.
     */
    public static function companyId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if ($user->role === 'owner') {
            return session('active_company_id');
        }

        return $user->company_id;
    }
}