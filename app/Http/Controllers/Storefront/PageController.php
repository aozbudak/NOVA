<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __invoke(string $page): View
    {
        $pages = [
            'about' => ['title' => 'About NOVA', 'kicker' => 'The House'],
            'contact' => ['title' => 'Contact', 'kicker' => 'Help'],
            'shipping' => ['title' => 'Shipping', 'kicker' => 'Help'],
            'returns' => ['title' => 'Returns', 'kicker' => 'Help'],
            'faq' => ['title' => 'FAQ', 'kicker' => 'Help'],
            'size-guide' => ['title' => 'Size Guide', 'kicker' => 'Help'],
            'careers' => ['title' => 'Careers', 'kicker' => 'About NOVA'],
            'sustainability' => ['title' => 'Sustainability', 'kicker' => 'About NOVA'],
            'privacy' => ['title' => 'Privacy', 'kicker' => 'Legal'],
            'terms' => ['title' => 'Terms', 'kicker' => 'Legal'],
            'cookies' => ['title' => 'Cookies', 'kicker' => 'Legal'],
        ];

        abort_unless(array_key_exists($page, $pages), 404);

        return view('storefront.page', [
            'page' => $page,
            'meta' => $pages[$page],
        ]);
    }
}
