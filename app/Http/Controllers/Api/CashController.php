<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function show(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->cashRegister()]);
    }

    public function movements(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->cashMovements()]);
    }

    public function open(Request $request, DatabaseRecords $records): JsonResponse
    {
        $validated = $request->validate([
            'opening' => ['nullable', 'numeric', 'min:0'],
        ]);

        $records->openRegister((float) ($validated['opening'] ?? 0));

        return response()->json([
            'status' => 'opened',
            'message' => __('admin.toast.register_opened'),
        ]);
    }

    public function close(Request $request, DatabaseRecords $records): JsonResponse
    {
        $validated = $request->validate([
            'actual' => ['nullable', 'numeric', 'min:0'],
        ]);

        $records->closeRegister(isset($validated['actual']) ? (float) $validated['actual'] : null);

        return response()->json([
            'status' => 'closed',
            'message' => __('admin.toast.register_closed'),
        ]);
    }
}
