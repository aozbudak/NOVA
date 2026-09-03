<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin.authenticated')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(401);
            }

            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
