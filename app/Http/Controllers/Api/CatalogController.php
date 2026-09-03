<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request, Catalog $catalog): JsonResponse
    {
        $products = $catalog->all();
        $brand = $request->string('brand')->toString();

        if ($brand !== '') {
            $products = $catalog->browse([
                'department' => 'collections',
                'brand' => $brand,
                'sort' => $request->string('sort')->toString() ?: 'recommended',
            ]);
        }

        return response()->json([
            'data' => $products->values(),
        ]);
    }

    public function show(string $product, Catalog $catalog): JsonResponse
    {
        $record = $catalog->findBySlug($product) ?? $catalog->find((int) $product);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
