<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeController extends Controller
{
    public function __invoke(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
        ];

        return view('admin.barcode.index', [
            'barcodes' => AdminList::apply(
                $store->barcodes($filters),
                ['product', 'sku', 'barcode', 'stock'],
            ),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
            ]),
        ]);
    }
}
