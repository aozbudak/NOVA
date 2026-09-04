<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __invoke(string $page, SiteContent $site): View
    {
        $content = $site->page($page);

        abort_if($content === null, 404);

        return view('storefront.page', [
            'page' => $page,
            'content' => $content,
            'meta' => [
                'title' => $content['title'],
                'kicker' => $content['kicker'],
            ],
        ]);
    }
}
