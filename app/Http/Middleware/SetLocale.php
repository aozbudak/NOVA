<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->locale($request));

        return $next($request);
    }

    private function locale(Request $request): string
    {
        $available = array_keys(config('app.available_locales'));
        $candidates = [
            $request->session()->get('locale'),
            $request->cookie('locale'),
            $request->getPreferredLanguage($available),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $available, true)) {
                return $candidate;
            }
        }

        return config('app.locale');
    }
}
