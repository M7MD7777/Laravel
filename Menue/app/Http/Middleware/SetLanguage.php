<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    public function handle($request, Closure $next)
    {
        // Get language from request or use the default language ('en' in this case)
        $language = $request->header('Accept-Language', 'en');

        // Check if the language is supported, otherwise fallback to English
        $supportedLanguages = ['en', 'ar']; // Add more languages if needed
        $language = in_array($language, $supportedLanguages) ? $language : 'en';

        // Set the application locale
        App::setLocale($language);

        return $next($request);
    }
}
