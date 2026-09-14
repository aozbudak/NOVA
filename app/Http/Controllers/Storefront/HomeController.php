<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Catalog $catalog): View
    {
        return view('storefront.home', [
            'products' => $catalog->featured(),
            'campaigns' => $catalog->campaigns(),
            'heroImage' => $catalog->heroImage(),
        ]);
    }
}
