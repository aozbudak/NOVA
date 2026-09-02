<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, AdminStore $store): JsonResponse
    {
        $products = $store->filteredProducts([
            'search' => $request->string('search')->toString(),
            'category' => $request->string('category')->toString(),
            'brand' => $request->string('brand')->toString(),
            'status' => $request->string('status')->toString(),
            'stock' => $request->string('stock')->toString(),
        ]);

        return response()->json(['data' => $products->values()]);
    }

    public function store(Request $request, DatabaseRecords $records): JsonResponse
    {
        $records->saveProduct($request->only([
            'name', 'description', 'category', 'brand', 'status',
            'purchase_price', 'price', 'vat', 'variants', 'initial_stock', 'min_stock',
        ]));

        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.product_created'),
        ], 201);
    }

    public function show(string $product, AdminStore $store): JsonResponse
    {
        $record = $store->product($product);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }

    public function update(Request $request, string $product, AdminStore $store, DatabaseRecords $records): JsonResponse
    {
        abort_if($store->product($product) === null, 404);

        $records->saveProduct($request->only([
            'name', 'description', 'category', 'brand', 'status',
            'purchase_price', 'price', 'vat', 'variants', 'initial_stock', 'min_stock',
        ]), $product);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.product_updated'),
        ]);
    }

    public function deactivate(string $product, AdminStore $store, DatabaseRecords $records): JsonResponse
    {
        abort_if($store->product($product) === null, 404);

        $records->deactivateProduct($product);

        return response()->json([
            'status' => 'deactivated',
            'message' => __('admin.toast.product_deactivated'),
        ]);
    }
}
