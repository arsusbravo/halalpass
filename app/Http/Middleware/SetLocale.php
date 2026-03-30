<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        // Priority: 1) session, 2) browser language, 3) app config default
        $locale = session('locale')
            ?? $this->detectBrowserLocale($request)
            ?? config('app.locale', 'id');

        if (in_array($locale, self::SUPPORTED_LOCALES)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse "id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7" format
        $preferred = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);

        return $preferred;
    }
}