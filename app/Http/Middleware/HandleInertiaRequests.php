<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Active company context
            'activeCompany' => fn () => $this->getActiveCompany($request),

            // i18n
            'locale' => fn () => App::getLocale(),
            'translations' => fn () => $this->getTranslations(),

            // Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    private function getActiveCompany(Request $request): ?array
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $companyId = $user->activeCompanyId();

        if (!$companyId) {
            return null;
        }

        $company = Company::find($companyId);

        return $company ? [
            'id' => $company->id,
            'name' => $company->name,
        ] : null;
    }

    private function getTranslations(): array
    {
        $locale = App::getLocale();
        $file = lang_path("{$locale}.json");

        if (!File::exists($file)) {
            return [];
        }

        return json_decode(File::get($file), true) ?? [];
    }
}