<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __invoke(AdminStore $store): View
    {
        return view('admin.brands.index', [
            'brands' => AdminList::apply($store->brandRecords(), ['name', 'products', 'stock', 'status']),
        ]);
    }
}
