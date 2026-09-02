<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request, AdminStore $store): JsonResponse
    {
        return response()->json([
            'data' => $store->suppliers([
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'date' => $request->string('date')->toString(),
                'sort' => $request->string('sort')->toString(),
            ])->values(),
        ]);
    }

    public function store(): JsonResponse
    {
        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.supplier_created'),
        ], 201);
    }

    public function show(string $supplier, AdminStore $store): JsonResponse
    {
        $record = $store->supplier($supplier);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
