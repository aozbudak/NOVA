<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'date' => $request->string('date')->toString(),
            'sort' => $request->string('sort')->toString(),
        ];

        return view('admin.suppliers.index', [
            'suppliers' => $store->suppliers($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.suppliers.form');
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.suppliers.index')
            ->with('status', __('admin.toast.supplier_created'));
    }

    public function show(string $supplier, AdminStore $store): View
    {
        $record = $store->supplier($supplier);

        abort_if($record === null, 404);

        return view('admin.suppliers.show', [
            'supplier' => $record,
        ]);
    }
}
