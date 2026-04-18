<?php

use App\Http\Controllers\AuditExportController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SjphController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPortalController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\CertificationReadinessController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Language switcher
Route::get('locale/{locale}', LocaleController::class)->name('locale.switch');

// ============================================================
//  Authenticated routes
// ============================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard — accessible even without company context (shows prompt for owner)
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // --------------------------------------------------------
    //  Owner-only: Company management (platform level)
    // --------------------------------------------------------
    Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::patch('companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');
    Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('companies/{company}/enter', [CompanyController::class, 'enter'])->name('companies.enter');
    Route::post('companies/leave', [CompanyController::class, 'leave'])->name('companies.leave');

    // --------------------------------------------------------
    //  Company-scoped routes (requires company context)
    // --------------------------------------------------------
    Route::middleware('company.scope')->group(function () {

        // Company profile — view for all, edit for admin
        Route::get('company-profile', [CompanyProfileController::class, 'show'])->name('company-profile.show');
        Route::put('company-profile', [CompanyProfileController::class, 'update'])->name('company-profile.update');

        // User management — admin only
        Route::resource('users', UserManagementController::class)->except(['show']);

        // Facilities
        Route::resource('facilities', FacilityController::class);

        // Suppliers
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/{supplier}/generate-token', [SupplierController::class, 'generateToken'])
            ->name('suppliers.generate-token');
        Route::delete('supplier-tokens/{token}', [SupplierController::class, 'revokeToken'])
            ->name('supplier-tokens.revoke');

        // Ingredients
        Route::get('ingredients/bulk', [IngredientController::class, 'bulkCreate'])->name('ingredients.bulk-create');
        Route::get('ingredients/search', [IngredientController::class, 'search'])->name('ingredients.search');
        Route::resource('ingredients', IngredientController::class);
        Route::post('ingredients/{ingredient}/children', [IngredientController::class, 'addChild'])
            ->name('ingredients.add-child');
        Route::post('ingredients/bulk', [IngredientController::class, 'bulkStore'])->name('ingredients.bulk-store');

        // Halal Certificates
        Route::resource('certificates', CertificateController::class);
        Route::post('certificates/{certificate}/upload', [CertificateController::class, 'uploadDocument'])
            ->name('certificates.upload');
        Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])
            ->name('certificates.download');

        Route::get('certification', CertificationReadinessController::class)->name('certification.readiness');

        // Products
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/ingredients', [ProductController::class, 'addIngredient'])
            ->name('products.add-ingredient');
        Route::delete('products/{product}/ingredients/{ingredient}', [ProductController::class, 'removeIngredient'])
            ->name('products.remove-ingredient');
        Route::post('products/{product}/recalculate', [ProductController::class, 'recalculate'])
            ->name('products.recalculate');

        // SJPH Documents
        Route::get('sjph/{facility}', [SjphController::class, 'show'])->name('sjph.show');
        Route::post('sjph/{sjphDocument}/section', [SjphController::class, 'saveSection'])->name('sjph.save-section');
        Route::post('sjph/{sjphDocument}/submit', [SjphController::class, 'submit'])->name('sjph.submit');
        Route::post('sjph/{sjphDocument}/approve', [SjphController::class, 'approve'])->name('sjph.approve');
        Route::post('sjph/{sjphDocument}/reject', [SjphController::class, 'reject'])->name('sjph.reject');
        Route::post('sjph/{sjphDocument}/new-version', [SjphController::class, 'newVersion'])->name('sjph.new-version');

        // Audit Export
        Route::get('export', [AuditExportController::class, 'index'])->name('export.index');
        Route::get('export/generate', [AuditExportController::class, 'generate'])->name('export.generate');
    });
});

// ============================================================
//  Supplier Portal (no auth — token-based guest access)
// ============================================================
Route::prefix('supplier-portal')->group(function () {
    Route::get('{token}', [SupplierPortalController::class, 'show'])->name('supplier-portal.show');
    Route::post('{token}/upload', [SupplierPortalController::class, 'upload'])->name('supplier-portal.upload');
});

require __DIR__ . '/settings.php';