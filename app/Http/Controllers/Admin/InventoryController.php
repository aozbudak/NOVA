<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'category' => $request->string('category')->toString(),
            'stock' => $request->string('stock')->toString(),
        ];

        return view('admin.inventory.index', [
            'rows' => AdminList::apply(
                $store->inventory($filters),
                ['product', 'sku', 'stock', 'status'],
            ),
            'categories' => $store->categories(),
            'products' => $store->products(),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'category' => ['label' => __('admin.inventory.filter_category')],
                'stock' => [
                    'label' => __('admin.inventory.filter_stock'),
                    'value' => $filters['stock'] === '' ? '' : __('admin.stock.'.$filters['stock']),
                ],
            ]),
        ]);
    }

    public function movements(AdminStore $store): View
    {
        return view('admin.inventory.movements', [
            'movements' => AdminList::apply(collect($store->movements()), ['date', 'product', 'type', 'qty']),
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
        ]);

        $sku = $validated['sku'] ?? '';

        if ($sku === '' && filled($validated['variant_id'] ?? null)) {
            $sku = (string) ($store->variants()->firstWhere('id', $validated['variant_id'])['sku'] ?? '');
        }

        abort_if($sku === '' || $store->variants()->firstWhere('sku', $sku) === null, 404);

        $quantity = (int) $validated['quantity'];
        $type = $validated['type'] ?? 'adjustment';
        $note = $validated['note'] ?? $validated['reason'] ?? null;

        if ($type === 'in' && $quantity < 0) {
            $quantity = abs($quantity);
        }

        if ($type === 'out' && $quantity > 0) {
            $quantity = -abs($quantity);
            $type = 'out';
        }

        $records->adjustStock($sku, $quantity, $note, $type);

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', __('admin.toast.stock_updated'));
    }
}
