<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;

class PosController extends Controller
{
    public function items(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->posItems()->values()]);
    }
}
