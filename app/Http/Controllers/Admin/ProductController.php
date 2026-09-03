<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use App\Support\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'category' => $request->string('category')->toString(),
            'brand' => $request->string('brand')->toString(),
            'status' => $request->string('status')->toString(),
            'stock' => $request->string('stock')->toString(),
        ];

        return view('admin.products.index', [
            'products' => AdminList::apply(
                $store->filteredProducts($filters),
                ['name', 'sku', 'category', 'price', 'stock', 'status'],
            ),
            'categories' => $store->categories(),
            'brands' => $store->brands(),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'category' => ['label' => __('admin.products.filter_category')],
                'brand' => ['label' => __('admin.products.filter_brand')],
                'status' => [
                    'label' => __('admin.products.filter_status'),
                    'value' => $filters['status'] === '' ? '' : __('admin.products.status_'.$filters['status']),
                ],
                'stock' => [
                    'label' => __('admin.products.filter_stock'),
                    'value' => $filters['stock'] === '' ? '' : __('admin.stock.'.$filters['stock']),
                ],
            ]),
        ]);
    }

    public function create(AdminStore $store): View
    {
        return view('admin.products.form', [
            'product' => null,
            'categories' => $store->categoryOptions(),
            'brands' => $store->brandOptions()->where('status', 'active')->values(),
            'vatRates' => Price::vatRates(),
        ]);
    }

    public function store(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:180'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'vat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
            'variants.sku.*' => ['nullable', 'string', 'max:100'],
            'variants.barcode.*' => ['nullable', 'string', 'max:100'],
            'variants.color.*' => ['nullable', 'string', 'max:100'],
            'variants.size.*' => ['nullable', 'string', 'max:50'],
            'variants.price.*' => ['nullable', 'numeric', 'min:0'],
            'variants.stock.*' => ['nullable', 'integer', 'min:0'],
            'variants.is_active.*' => ['nullable', 'in:0,1'],
        ]);

        $records->saveProduct($request->only([
            'name', 'description', 'category', 'brand', 'status',
            'purchase_price', 'price', 'vat', 'variants', 'initial_stock',
        ]));

        return redirect()
            ->route('admin.products.index')
            ->with('status', __('admin.toast.product_created'));
    }

    public function edit(string $product, AdminStore $store): View
    {
        $record = $store->product($product);

        abort_if($record === null, 404);

        return view('admin.products.form', [
            'product' => $record,
            'categories' => $store->categoryOptions(),
            'brands' => $store->brandOptions()->where('status', 'active')->values(),
            'vatRates' => Price::vatRates(),
        ]);
    }

    public function update(Request $request, string $product, AdminStore $store, DatabaseRecords $records): RedirectResponse
    {
        abort_if($store->product($product) === null, 404);

        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:180'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'vat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
            'variants.sku.*' => ['nullable', 'string', 'max:100'],
            'variants.barcode.*' => ['nullable', 'string', 'max:100'],
            'variants.color.*' => ['nullable', 'string', 'max:100'],
            'variants.size.*' => ['nullable', 'string', 'max:50'],
            'variants.price.*' => ['nullable', 'numeric', 'min:0'],
            'variants.stock.*' => ['nullable', 'integer', 'min:0'],
            'variants.is_active.*' => ['nullable', 'in:0,1'],
        ]);

        $records->saveProduct($request->only([
            'name', 'description', 'category', 'brand', 'status',
            'purchase_price', 'price', 'vat', 'variants', 'initial_stock',
        ]), $product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', __('admin.toast.product_updated'));
    }

    public function deactivate(string $product, AdminStore $store, DatabaseRecords $records): RedirectResponse
    {
        abort_if($store->product($product) === null, 404);

        $records->deactivateProduct($product);

        return redirect()
            ->route('admin.products.index')
            ->with('status', __('admin.toast.product_deactivated'));
    }
}
