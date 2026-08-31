<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use App\Support\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Wishlist $wishlist): View
    {
        return view('storefront.wishlist', [
            'products' => $wishlist->products(),
        ]);
    }

    public function store(Request $request, Wishlist $wishlist, Catalog $catalog): JsonResponse|RedirectResponse
    {
        $productIds = $catalog->all()->pluck('id')->all();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::in($productIds)],
        ]);

        $added = $wishlist->toggle((int) $validated['product_id']);

        if ($request->wantsJson()) {
            return response()->json([
                'added' => $added,
                'ids' => $wishlist->ids(),
            ]);
        }

        return back();
    }
}
