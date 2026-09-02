<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(Catalog $catalog): JsonResponse
    {
        return response()->json([
            'data' => $catalog->all()->values(),
        ]);
    }

    public function show(string $product, Catalog $catalog): JsonResponse
    {
        $record = $catalog->findBySlug($product) ?? $catalog->find((int) $product);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
