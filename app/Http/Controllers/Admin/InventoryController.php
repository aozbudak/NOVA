<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'category' => $request->string('category')->toString(),
            'stock' => $request->string('stock')->toString(),
            'supplier' => $request->string('supplier')->toString(),
        ];

        return view('admin.inventory.index', [
            'rows' => AdminList::apply(
                $store->inventory($filters),
                ['product', 'sku', 'barcode', 'stock', 'status', 'supplier'],
            ),
            'categories' => $store->categories(),
            'products' => $store->products(),
            'suppliers' => $store->suppliers(['status' => 'active']),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'category' => ['label' => __('admin.inventory.filter_category')],
                'stock' => [
                    'label' => __('admin.inventory.filter_stock'),
                    'value' => $filters['stock'] === '' ? '' : __('admin.stock.'.$filters['stock']),
                ],
                'supplier' => [
                    'label' => __('admin.inventory.supplier'),
                    'value' => $filters['supplier'] === ''
                        ? ''
                        : (string) ($store->suppliers()->firstWhere('id', $filters['supplier'])['name'] ?? $filters['supplier']),
                ],
            ]),
        ]);
    }

    public function movements(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'product' => $request->string('product')->toString(),
        ];

        return view('admin.inventory.movements', [
            'movements' => AdminList::apply(
                collect($store->movements($filters)),
                ['date', 'product', 'barcode', 'type', 'qty'],
            ),
            'products' => $store->products(),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'product' => ['label' => __('admin.inventory.product')],
            ]),
        ]);
    }

    public function adjust(Request $request, AdminStore $store, DatabaseRecords $records): RedirectResponse
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

        if ($type === 'in' && $quantity < 0) {
            $quantity = abs($quantity);
        }

        if ($type === 'out' && $quantity > 0) {
            $quantity = -abs($quantity);
            $type = 'out';
        }

        if ($type === 'in' && filled($validated['supplier_id'] ?? null)) {
            $supplierId = $records->resolveSupplierUuid(
                $validated['supplier_id'],
                $store->suppliers()->firstWhere('id', $validated['supplier_id']),
            );
        }

        $records->adjustStock($sku, $quantity, $note, $type, $supplierId);

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', __('admin.toast.stock_updated'));
    }
}
