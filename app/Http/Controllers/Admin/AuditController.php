<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
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
            'logs' => AdminList::apply($store->auditLogs($filters), ['datetime', 'user', 'module', 'status']),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
                'module' => ['label' => __('admin.audit.module')],
                'user' => ['label' => __('admin.audit.user')],
                'status' => [
                    'label' => __('admin.audit.status'),
                    'value' => $filters['status'] === '' ? '' : __('admin.status.'.$filters['status']),
                ],
            ]),
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
