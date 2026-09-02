<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        return view('admin.inventory.index', [
            'rows' => $store->inventory([
                'search' => $request->string('search')->toString(),
                'category' => $request->string('category')->toString(),
                'stock' => $request->string('stock')->toString(),
            ]),
            'categories' => $store->categories(),
        ]);
    }

    public function movements(AdminStore $store): View
    {
        return view('admin.inventory.movements', [
            'movements' => $store->movements(),
        ]);
    }
}
