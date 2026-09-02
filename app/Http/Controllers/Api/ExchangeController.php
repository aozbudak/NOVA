<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;

class ExchangeController extends Controller
{
    public function index(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->exchanges()->values()]);
    }

    public function show(string $exchange, AdminStore $store): JsonResponse
    {
        $record = $store->exchange($exchange);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
