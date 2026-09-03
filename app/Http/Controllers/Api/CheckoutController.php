<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Cart;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function store(Request $request, Cart $cart, DatabaseRecords $records): JsonResponse
    {
        if ($cart->count() === 0) {
            return response()->json(['message' => __('storefront.cart.empty')], 422);
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

        $records->placeCheckout($cart, $validated, $orderId);

        session([
            'storefront.last_order' => [
                'id' => $orderId,
                'email' => $validated['email'],
                'total' => $cart->subtotal(),
            ],
        ]);

        $cart->clear();

        return response()->json([
            'id' => $orderId,
            'email' => $validated['email'],
            'confirmation_url' => route('checkout.confirmation'),
        ], 201);
    }
}
