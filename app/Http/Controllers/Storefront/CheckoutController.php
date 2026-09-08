<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Cart;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Cart $cart): View|RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('home');
        }

        return view('storefront.checkout', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'totals' => $cart->totals(),
            'reducedChrome' => true,
        ]);
    }

    public function store(Request $request, Cart $cart, DatabaseRecords $records): RedirectResponse
    {
        if ($cart->count() === 0) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:80'],
            'delivery' => ['required', 'in:standard,express'],
            'payment' => ['required', 'in:card,paypal'],
        ]);

        $orderId = 'NOVA-'.now()->format('ymdHis').'-'.str_pad((string) random_int(10, 99), 2, '0', STR_PAD_LEFT);

        $order = $records->placeCheckout($cart, $validated, $orderId);

        session([
            'storefront.last_order' => [
                'id' => $order?->order_number ?? $orderId,
                'email' => $validated['email'],
                'total' => $order?->total_amount ?? $cart->totals()['total'],
            ],
        ]);

        $cart->clear();

        return redirect()->route('checkout.confirmation');
    }

    public function confirmation(): View|RedirectResponse
    {
        $order = session('storefront.last_order');

        if (! is_array($order)) {
            return redirect()->route('home');
        }

        return view('storefront.confirmation', [
            'order' => $order,
            'reducedChrome' => true,
        ]);
    }
}
