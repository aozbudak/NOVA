<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Catalog $catalog): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.index', [
            'orders' => $catalog->sampleOrders(),
        ]);
    }

    public function orders(Catalog $catalog): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.orders', [
            'orders' => $catalog->sampleOrders(),
        ]);
    }

    public function profile(): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.profile');
    }

    public function addresses(): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.addresses');
    }

    public function settings(): View|RedirectResponse
    {
        return $this->guardedView('storefront.account.settings');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('storefront.customer');

        return redirect()->route('home');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardedView(string $view, array $data = []): View|RedirectResponse
    {
        if (! is_array(session('storefront.customer'))) {
            return redirect()->route('login');
        }

        return view($view, $data);
    }
}
