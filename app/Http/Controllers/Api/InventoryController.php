<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request, AdminStore $store): JsonResponse
    {
        return response()->json([
            'data' => $store->inventory([
                'search' => $request->string('search')->toString(),
                'category' => $request->string('category')->toString(),
                'stock' => $request->string('stock')->toString(),
            ])->values(),
        ]);
    }

    public function movements(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->movements()]);
    }

    public function adjust(Request $request, AdminStore $store, DatabaseRecords $records): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if($store->variants()->firstWhere('sku', $validated['sku']) === null, 404);

        $records->adjustStock($validated['sku'], (int) $validated['quantity'], $validated['reason'] ?? null);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.stock_updated'),
        ]);
    }
}
