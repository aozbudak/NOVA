<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelPageController extends Controller
{
    public function __invoke(Request $request, AdminNavigation $navigation): View
    {
        $routeName = $request->route()?->getName();
        $crumbs = $navigation->breadcrumbs($routeName);
        $title = $crumbs === [] ? __('admin.panel.title') : $crumbs[array_key_last($crumbs)]['label'];

        return view('admin.page', [
            'title' => $title,
            'description' => __('admin.page.placeholder'),
        ]);
    }
}
