<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'admin.authenticated',
            'admin.role',
            'admin.name',
            'admin.email',
            'admin.phone',
            'admin.user_id',
            'admin.username',
        ]);

        return redirect()->route('admin.login');
    }
}
