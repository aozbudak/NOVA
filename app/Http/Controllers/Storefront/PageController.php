<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * @var list<string>
     */
    private const PAGES = [
        'about',
        'contact',
        'shipping',
        'returns',
        'faq',
        'size-guide',
        'careers',
        'sustainability',
        'privacy',
        'terms',
        'cookies',
    ];

    public function __invoke(string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);

        return view('storefront.page', [
            'page' => $page,
            'meta' => [
                'title' => __('storefront.pages.'.$page.'.title'),
                'kicker' => __('storefront.pages.'.$page.'.kicker'),
            ],
        ]);
    }
}
