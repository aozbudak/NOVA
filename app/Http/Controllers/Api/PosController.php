<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function items(Request $request, AdminStore $store): JsonResponse
    {
        return response()->json([
            'data' => $store->posItems($request->string('q')->toString())->values(),
        ]);
    }
}
