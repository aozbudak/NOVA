<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
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

    public function open(): JsonResponse
    {
        return response()->json([
            'status' => 'opened',
            'message' => __('admin.toast.register_opened'),
        ]);
    }

    public function close(Request $request): JsonResponse
    {
        $request->validate([
            'actual' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json([
            'status' => 'closed',
            'message' => __('admin.toast.register_closed'),
        ]);
    }
}
