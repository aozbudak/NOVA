<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.reports.index', [
            'categories' => $store->reportCategories(),
        ]);
    }

    public function show(string $report, AdminStore $store): View
    {
        $record = $store->report($report);

        abort_if($record === null, 404);

        return view('admin.reports.show', [
            'report' => $record,
        ]);
    }
}
