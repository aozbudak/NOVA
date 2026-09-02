<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('storefront.auth.login');
    }

    public function login(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $customer = $records->authenticateCustomer($validated['email'], $validated['password']);

        if ($customer === false || $customer === null) {
            return back()->withErrors(['email' => __('auth.failed')]);
        }

        if ($customer->is_active === false || $customer->user?->is_active === false) {
            return back()->withErrors(['email' => __('auth.inactive')]);
        }

        $this->storeCustomerSession($customer, $validated['email']);

        return redirect()->route('account.show');
    }

    public function showRegister(): View
    {
        return view('storefront.auth.register');
    }

    public function register(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = $records->registerCustomer($validated);

        session([
            'storefront.customer' => [
                'first_name' => $customer?->first_name ?? $validated['first_name'],
                'last_name' => $customer?->last_name ?? $validated['last_name'],
                'email' => $customer?->email ?? $validated['email'],
                'phone' => (string) ($customer?->phone ?? ''),
            ],
        ]);

        return redirect()->route('account.show');
    }

    private function storeCustomerSession(?Customer $customer, string $email): void
    {
        if ($customer instanceof Customer) {
            session([
                'storefront.customer' => [
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'email' => (string) $customer->email,
                    'phone' => (string) ($customer->phone ?? ''),
                ],
            ]);

            return;
        }

        $name = strstr($email, '@', true) ?: 'Guest';

        session([
            'storefront.customer' => [
                'first_name' => ucfirst($name),
                'last_name' => '',
                'email' => $email,
                'phone' => '',
            ],
        ]);
    }
}
