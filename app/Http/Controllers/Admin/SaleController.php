<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
            'payment' => $request->string('payment')->toString(),
            'cashier' => $request->string('cashier')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        return view('admin.sales.index', [
            'sales' => AdminList::apply($store->sales($filters), ['number', 'date', 'customer', 'total', 'status']),
            'cashiers' => $store->cashiers(),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'from' => ['label' => __('admin.sales.from')],
                'to' => ['label' => __('admin.sales.to')],
                'payment' => [
                    'label' => __('admin.sales.filter_payment'),
                    'value' => $filters['payment'] === '' ? '' : __('admin.pos.'.$filters['payment']),
                ],
                'cashier' => ['label' => __('admin.sales.filter_cashier')],
                'status' => [
                    'label' => __('admin.sales.filter_status'),
                    'value' => $filters['status'] === '' ? '' : __('admin.status.'.$filters['status']),
                ],
            ]),
        ]);
    }

    public function show(string $sale, AdminStore $store): View
    {
        $record = $store->sale($sale);

        abort_if($record === null, 404);

        return view('admin.sales.show', [
            'sale' => $record,
        ]);
    }
}
