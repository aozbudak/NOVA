<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\View\View;

class VariantController extends Controller
{
    public function __invoke(AdminStore $store): View
    {
        return view('admin.variants.index', [
            'variants' => $store->variants(),
        ]);
    }
}
