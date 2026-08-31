<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function show(Request $request, Catalog $catalog, string $department, ?string $category = null): View
    {
        abort_unless(in_array($department, $catalog->departments(), true), 404);

        $filters = [
            'department' => $department,
            'category' => $category,
            'size' => $request->string('size')->toString() ?: null,
            'color' => $request->string('color')->toString() ?: null,
            'collection' => $request->string('collection')->toString() ?: null,
            'availability' => $request->string('availability')->toString() ?: null,
            'price' => $request->string('price')->toString() ?: null,
            'sort' => $request->string('sort')->toString() ?: 'recommended',
        ];

        $products = $catalog->browse($filters);
        $meta = $catalog->departmentMeta($department, $category);

        return view('storefront.listing', [
            'products' => $products,
            'department' => $department,
            'category' => $category,
            'filters' => $filters,
            'meta' => $meta,
        ]);
    }
}
