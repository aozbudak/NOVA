<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
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
            'returns' => $store->returns($filters),
            'filters' => $filters,
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

    public function store(): RedirectResponse
    {
        return redirect()->route('admin.returns.index');
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
