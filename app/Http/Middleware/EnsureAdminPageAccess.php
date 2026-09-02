<?php

namespace App\Http\Middleware;

use App\Support\AdminNavigation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPageAccess
{
    public function __construct(private AdminNavigation $navigation) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $permission = $this->navigation->permissionForRoute($request->route()?->getName());

        if ($permission === 'profile' || $permission === null) {
            return $next($request);
        }

        if ($this->navigation->staff()->role->can($permission)) {
            return $next($request);
        }

        abort(403);
    }
}
