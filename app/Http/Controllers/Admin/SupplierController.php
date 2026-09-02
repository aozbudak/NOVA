<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
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
            'suppliers' => AdminList::apply($store->suppliers($filters), ['name', 'total', 'last_purchase', 'status']),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'status' => [
                    'label' => __('admin.suppliers.filter_status'),
                    'value' => $filters['status'] === '' ? '' : __('admin.status.'.$filters['status']),
                ],
                'date' => ['label' => __('admin.suppliers.filter_date')],
                'sort' => ['label' => __('admin.suppliers.sort')],
            ]),
        ]);
    }

    public function create(): View
    {
        return view('admin.suppliers.form');
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
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
