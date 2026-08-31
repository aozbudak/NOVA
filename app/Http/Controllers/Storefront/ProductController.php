<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Catalog $catalog, string $slug): View
    {
        $product = $catalog->findBySlug($slug);

        abort_if($product === null, 404);

        return view('storefront.product', [
            'product' => $product,
            'related' => $catalog->related($product),
        ]);
    }
}
