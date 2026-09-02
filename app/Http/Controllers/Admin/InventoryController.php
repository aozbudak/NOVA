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
            'sku' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if($store->variants()->firstWhere('sku', $validated['sku']) === null, 404);

        $records->adjustStock($validated['sku'], (int) $validated['quantity'], $validated['reason'] ?? null);

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', __('admin.toast.stock_updated'));
    }
}
