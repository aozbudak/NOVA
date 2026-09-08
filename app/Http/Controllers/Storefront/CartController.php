<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Cart;
use App\Support\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function panel(Cart $cart): View
    {
        return view('storefront.partials.cart-panel', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'totals' => $cart->totals(),
        ]);
    }

    public function store(Request $request, Cart $cart, Catalog $catalog): JsonResponse|RedirectResponse
    {
        $productIds = $catalog->all()->pluck('id')->all();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::in($productIds)],
            'size' => ['required', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $cart->add(
            (int) $validated['product_id'],
            $validated['size'],
            (int) $validated['quantity'],
            $validated['color'] ?? null,
        );

        if ($request->wantsJson()) {
            return response()->json([
                'count' => $cart->count(),
                'message' => __('storefront.cart.added'),
            ]);
        }

        return back();
    }

    public function update(Request $request, Cart $cart, string $key): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $cart->update($key, (int) $validated['quantity']);

        if ($request->wantsJson()) {
            return response()->json([
                'count' => $cart->count(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return back();
    }

    public function destroy(Request $request, Cart $cart, string $key): JsonResponse|RedirectResponse
    {
        $cart->remove($key);

        if ($request->wantsJson()) {
            return response()->json([
                'count' => $cart->count(),
            ]);
        }

        return back();
    }
}
