<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request, AdminStore $store): JsonResponse
    {
        return response()->json([
            'data' => $store->returns([
                'return' => $request->string('return')->toString(),
                'sale' => $request->string('sale')->toString(),
                'customer' => $request->string('customer')->toString(),
                'date' => $request->string('date')->toString(),
                'reason' => $request->string('reason')->toString(),
                'status' => $request->string('status')->toString(),
            ])->values(),
        ]);
    }

    public function store(Request $request, AdminStore $store, DatabaseRecords $records): JsonResponse
    {
        $validated = $request->validate([
            'sale' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $notes = ($validated['reason'] ?? null) === 'other'
            ? trim((string) ($validated['notes'] ?? ''))
            : '';

        $persisted = $records->completeReturn(
            $validated['sale'],
            $validated['reason'] ?? null,
            $notes !== '' ? $notes : null,
        );

        if ($persisted === null) {
            abort_if($store->sale($validated['sale']) === null, 404);
        }

        return response()->json([
            'status' => 'completed',
            'message' => __('admin.toast.return_completed'),
        ], 201);
    }

    public function show(string $return, AdminStore $store): JsonResponse
    {
        $record = $store->returnRecord($return);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
