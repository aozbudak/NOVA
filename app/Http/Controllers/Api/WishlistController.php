<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use App\Support\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WishlistController extends Controller
{
    public function index(Wishlist $wishlist): JsonResponse
    {
        return response()->json([
            'ids' => $wishlist->ids(),
            'data' => $wishlist->products()->values(),
        ]);
    }

    public function store(Request $request, Wishlist $wishlist, Catalog $catalog): JsonResponse
    {
        $productIds = $catalog->all()->pluck('id')->all();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::in($productIds)],
        ]);

        $added = $wishlist->toggle((int) $validated['product_id']);

        return response()->json([
            'added' => $added,
            'ids' => $wishlist->ids(),
        ]);
    }
}
