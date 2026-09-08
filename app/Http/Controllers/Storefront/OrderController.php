<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\CustomerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(string $order, CustomerAccount $account): View|RedirectResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return redirect()->route('login');
        }

        return view('storefront.account.order', [
            'order' => $account->detailOrder($account->order($customer, $order)),
        ]);
    }
}
