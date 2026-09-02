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

    public function store(Request $request, AdminStore $store): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'address' => ['nullable', 'string', 'max:255'],
            'tax' => ['nullable', 'string', 'max:255'],
        ]);

        $store->createSupplier($data);

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
