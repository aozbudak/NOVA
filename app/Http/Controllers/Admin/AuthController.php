<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (session('admin.authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $staff = $store->staffByUsername($validated['username']);

        if ($staff === null || ! $store->passwordMatches($staff, $validated['password'])) {
            return back()
                ->withErrors(['username' => __('auth.failed')])
                ->onlyInput('username');
        }

        if (($staff['status'] ?? 'active') !== 'active') {
            return back()
                ->withErrors(['username' => __('auth.inactive')])
                ->onlyInput('username');
        }

        $store->touchLastLogin((string) $staff['id']);

        $request->session()->put([
            'admin.authenticated' => true,
            'admin.role' => $staff['role'],
            'admin.name' => $staff['name'],
            'admin.email' => $staff['email'],
            'admin.phone' => $staff['phone'] ?? '',
            'admin.user_id' => $staff['id'],
            'admin.username' => $staff['username'],
            'admin.abilities' => $staff['abilities'] ?? [],
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }
}
