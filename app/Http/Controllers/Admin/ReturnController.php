<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'return' => $request->string('return')->toString(),
            'sale' => $request->string('sale')->toString(),
            'customer' => $request->string('customer')->toString(),
            'date' => $request->string('date')->toString(),
            'reason' => $request->string('reason')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        return view('admin.returns.index', [
            'returns' => AdminList::apply($store->returns($filters), ['number', 'date', 'amount', 'status']),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'return' => ['label' => __('admin.returns.filter_return')],
                'sale' => ['label' => __('admin.returns.filter_sale')],
                'customer' => ['label' => __('admin.returns.filter_customer')],
                'date' => ['label' => __('admin.returns.date')],
                'reason' => [
                    'label' => __('admin.returns.reason'),
                    'value' => $filters['reason'] === '' ? '' : __('admin.status.'.$filters['reason']),
                ],
                'status' => [
                    'label' => __('admin.returns.status'),
                    'value' => $filters['status'] === '' ? '' : __('admin.status.'.$filters['status']),
                ],
            ]),
        ]);
    }

    public function create(Request $request, AdminStore $store): View
    {
        $query = $request->string('sale')->trim()->toString();
        $sale = $query === '' ? null : $store->sale($query);

        return view('admin.returns.create', [
            'query' => $query,
            'sale' => $sale,
        ]);
    }

    public function store(Request $request, AdminStore $store, DatabaseRecords $records): RedirectResponse
    {
        $validated = $request->validate([
            'sale' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (filled($validated['sale'] ?? null)) {
            $fromStore = $store->sale($validated['sale']);
            $persisted = $records->completeReturn($validated['sale'], $validated['reason'] ?? null);
            abort_if($fromStore === null && $persisted === null, 404);
        }

        return redirect()
            ->route('admin.returns.index')
            ->with('status', __('admin.toast.return_completed'));
    }

    public function show(string $return, AdminStore $store): View
    {
        $record = $store->returnRecord($return);

        abort_if($record === null, 404);

        return view('admin.returns.show', [
            'return' => $record,
        ]);
    }
}
