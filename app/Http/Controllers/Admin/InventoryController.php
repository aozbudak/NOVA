<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        return view('admin.inventory.index', [
            'rows' => $store->inventory([
                'search' => $request->string('search')->toString(),
                'category' => $request->string('category')->toString(),
                'stock' => $request->string('stock')->toString(),
            ]),
            'categories' => $store->categories(),
        ]);
    }

    public function movements(AdminStore $store): View
    {
        return view('admin.inventory.movements', [
            'movements' => $store->movements(),
        ]);
    }

    public function adjust(Request $request, AdminStore $store): RedirectResponse
    {
        $request->validate([
            'sku' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if($store->variants()->firstWhere('sku', $request->string('sku')->toString()) === null, 404);

        return redirect()
            ->route('admin.inventory.index')
            ->with('status', __('admin.toast.stock_updated'));
    }
}
