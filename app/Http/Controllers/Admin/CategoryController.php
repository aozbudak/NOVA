<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __invoke(AdminStore $store): View
    {
        return view('admin.categories.index', [
            'categories' => AdminList::apply($store->categoryRecords(), ['name', 'products', 'stock', 'status']),
        ]);
    }
}
