<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, Catalog $catalog): View|JsonResponse
    {
        $query = $request->string('q')->trim()->toString();
        $products = $query === '' ? collect() : $catalog->search($query);

        if ($request->wantsJson()) {
            return response()->json(
                $products->take(8)->map(fn (array $product): array => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'currency' => $product['currency'],
                    'image' => $product['images'][0] ?? '',
                ])->values()
            );
        }

        return view('storefront.search', [
            'query' => $query,
            'products' => $products,
        ]);
    }
}
