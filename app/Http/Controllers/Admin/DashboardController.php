<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminNavigation;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AdminNavigation $navigation, AdminStore $store): View|RedirectResponse
    {
        if (! $navigation->staff()->can('dashboard')) {
            return redirect()->route($navigation->homeRoute());
        }

        $range = $request->string('range')->toString();

        if (! in_array($range, ['today', '7d', '30d', 'custom'], true)) {
            $range = 'today';
        }

        return view('admin.dashboard', $store->dashboard($range, $navigation->staff()));
    }
}
