<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'sales' => $store->sales($filters),
            'cashiers' => $store->cashiers(),
            'filters' => $filters,
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
