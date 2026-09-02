<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'user' => $request->string('user')->toString(),
            'module' => $request->string('module')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        return view('admin.audit.index', [
            'logs' => $store->auditLogs($filters),
            'filters' => $filters,
        ]);
    }

    public function show(string $audit, AdminStore $store): View
    {
        $record = $store->auditLog($audit);

        abort_if($record === null, 404);

        return view('admin.audit.show', [
            'log' => $record,
        ]);
    }
}
