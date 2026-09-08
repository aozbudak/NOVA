<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Catalog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function show(Request $request, Catalog $catalog, string $discount): View
    {
        $record = $catalog->findDiscount($discount);

        abort_if($record === null, 404);

        $filters = [
            'department' => 'sale',
            'discount' => $record->id,
            'category' => $request->string('category')->toString() ?: null,
            'size' => $request->string('size')->toString() ?: null,
            'color' => $request->string('color')->toString() ?: null,
            'collection' => $request->string('collection')->toString() ?: null,
            'availability' => $request->string('availability')->toString() ?: null,
            'price' => $request->string('price')->toString() ?: null,
            'sort' => $request->string('sort')->toString() ?: 'recommended',
        ];

        $products = $catalog->browseDiscount($record, $filters);
        $page = max(1, $request->integer('page'));
        $perPage = 12;
        $paginated = new LengthAwarePaginator(
            $products->forPage($page, $perPage)->values(),
            $products->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('storefront.discount', [
            'discount' => $record,
            'products' => $paginated,
            'department' => 'sale',
            'category' => $filters['category'],
            'filters' => $filters,
            'meta' => [
                'title' => $record->name,
                'label' => $record->name,
                'breadcrumb' => $record->name,
            ],
            'categories' => $catalog->categoriesFor('sale'),
        ]);
    }
}
