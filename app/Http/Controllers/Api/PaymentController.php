<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __invoke(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->payments()->values()]);
    }
}
