<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request, AdminStore $store): JsonResponse
    {
        return response()->json([
            'data' => $store->inventory([
                'search' => $request->string('search')->toString(),
                'category' => $request->string('category')->toString(),
                'stock' => $request->string('stock')->toString(),
                'supplier' => $request->string('supplier')->toString(),
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
            'sku' => ['nullable', 'string', 'max:255'],
            'variant_id' => ['nullable', 'string', 'max:36'],
            'type' => ['nullable', 'in:in,out'],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'string', 'max:255', Rule::in($store->suppliers()->pluck('id')->all())],
        ]);

        $sku = $validated['sku'] ?? '';

        if ($sku === '' && filled($validated['variant_id'] ?? null)) {
            $sku = (string) ($store->variants()->firstWhere('id', $validated['variant_id'])['sku'] ?? '');
        }

        abort_if($sku === '' || $store->variants()->firstWhere('sku', $sku) === null, 404);

        $quantity = (int) $validated['quantity'];
        $type = $validated['type'] ?? 'adjustment';
        $note = $validated['note'] ?? $validated['reason'] ?? null;
        $supplierId = null;

        if ($type === 'out' && $quantity > 0) {
            $quantity = -abs($quantity);
        }

        if ($type === 'in' && filled($validated['supplier_id'] ?? null)) {
            $supplierId = $records->resolveSupplierUuid(
                $validated['supplier_id'],
                $store->suppliers()->firstWhere('id', $validated['supplier_id']),
            );
        }

        $records->adjustStock($sku, $quantity, $note, $type, $supplierId);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.stock_updated'),
        ]);
    }
}
