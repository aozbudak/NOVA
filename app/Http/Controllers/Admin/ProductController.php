<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
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
            'products' => $store->filteredProducts($filters),
            'categories' => $store->categories(),
            'brands' => $store->brands(),
            'filters' => $filters,
        ]);
    }

    public function create(AdminStore $store): View
    {
        return view('admin.products.form', [
            'product' => null,
            'categories' => $store->categories(),
            'brands' => $store->brands(),
        ]);
    }

    public function store(): RedirectResponse
    {
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
            'categories' => $store->categories(),
            'brands' => $store->brands(),
        ]);
    }

    public function update(string $product, AdminStore $store): RedirectResponse
    {
        abort_if($store->product($product) === null, 404);

        return redirect()
            ->route('admin.products.index')
            ->with('status', __('admin.toast.product_updated'));
    }

    public function deactivate(string $product, AdminStore $store): RedirectResponse
    {
        abort_if($store->product($product) === null, 404);

        return redirect()
            ->route('admin.products.index')
            ->with('status', __('admin.toast.product_deactivated'));
    }
}
