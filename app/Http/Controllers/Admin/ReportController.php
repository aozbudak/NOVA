<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\ReportExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.reports.index', [
            'categories' => $store->reportCategories(),
        ]);
    }

    public function show(Request $request, string $report, AdminStore $store): View
    {
        $record = $store->report($report, $this->filters($request));

        abort_if($record === null, 404);

        return view('admin.reports.show', [
            'report' => $record,
        ]);
    }

    public function export(Request $request, string $report, AdminStore $store, ReportExport $export): StreamedResponse
    {
        $record = $store->report($report, $this->filters($request));

        abort_if($record === null, 404);

        return $export->download($record, $request->string('format')->toString());
    }

    /**
     * @return array{range?: string, from?: string, to?: string, date?: string, category?: string, brand?: string, stock?: string}
     */
    private function filters(Request $request): array
    {
        return [
            'range' => $request->string('range')->toString(),
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
            'date' => $request->string('date')->toString(),
            'category' => $request->string('category')->toString(),
            'brand' => $request->string('brand')->toString(),
            'stock' => $request->string('stock')->toString(),
        ];
    }
}
