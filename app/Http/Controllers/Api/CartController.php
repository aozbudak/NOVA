<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Cart;
use App\Support\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index(Cart $cart): JsonResponse
    {
        return response()->json([
            'count' => $cart->count(),
            'subtotal' => $cart->subtotal(),
            'items' => $cart->items()->values(),
        ]);
    }

    public function store(Request $request, Cart $cart, Catalog $catalog): JsonResponse
    {
        $productIds = $catalog->all()->pluck('id')->all();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::in($productIds)],
            'size' => ['required', 'string', 'max:8'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $cart->add((int) $validated['product_id'], $validated['size'], (int) $validated['quantity']);

        return response()->json([
            'count' => $cart->count(),
            'message' => __('storefront.cart.added'),
        ]);
    }

    public function update(Request $request, Cart $cart, string $key): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $cart->update($key, (int) $validated['quantity']);

        return response()->json([
            'count' => $cart->count(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function destroy(Cart $cart, string $key): JsonResponse
    {
        $cart->remove($key);

        return response()->json([
            'count' => $cart->count(),
        ]);
    }
}
